<?php

namespace Pum\Core\Extension\Notification\Service;

use Doctrine\ORM\EntityManager;
use Pum\Bundle\CoreBundle\Entity\Notification;
use Pum\Core\Extension\Notification\Entity\IUserNotificationInterface;
use Pum\Core\Extension\Notification\Entity\IGroupNotificationInterface;
use Pum\Bundle\AppBundle\Entity\UserRepository;

/**
 * Class NotificationService
 * @package Pum\Bundle\WoodworkBundle\Services
 */
class NotificationService
{
    private $entityManager;
    private $twig;
    private $mailer;

    private $options = array();

    public function __construct(\Twig_Environment $twig, EntityManager $entityManager, \Swift_Mailer $mailer)
    {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    public function setDefaultOptions(array $options)
    {
        $this->options = $options;
    }

    public function create(array $options)
    {
        $options = array_replace_recursive($this->options, $options);

        $notification = new Notification();
        $notification->setEmail(false);

        // Throw an exception if required fields are missing
        if (!isset($options['content']['title']) || !isset($options['content']['body'])) {
            throw new \Exception('Notification: missing fields title or body');
        }

        if (isset($options['email'])) {
            if ($options['email'] === true) {
                // Exception mail property is a boolean and set to true, this notification's email will be send immediately
                $notification->setEmail(true);
            } elseif ($options['email'] instanceof \DateTime) {
                // Email property is a DateTime, this notification's email will be delayed
                $notification->setEmail(true);
                $notification->setDelayed($options['email']);
            } elseif (is_string($options['email'])) {
                // Email property is a String, this notification's email will be delayed if the string can be successfully converted to a DateTime
                $date = \DateTime::createFromFormat('Y-m-d H:i', $options['email']);
                if (!$date) {
                    $date = \DateTime::createFromFormat('H:i', $options['email']);

                    if (!$date) {
                        throw new \Exception('Notification: Invalid email date format, use Y-m-d H:i or H:i format');
                    }

                    if (new \DateTime() > $date) {
                        $date->add(new \DateInterval('P1D'));
                    }
                }

                $notification->setEmail(true);
                $notification->setDelayed($date);
            }
        }

        // Set notification title
        $notification->setContentTitle($options['content']['title']);

        // Set notification body, either a twig template or a string
        if (is_array($options['content']['body'])) {
            $template = reset($options['content']['body']);
            $vars = next($options['content']['body']);

            if (!$vars) {
                $vars = array();
            }

            $notification->setContentBody($this->twig->render($template, $vars));
        } else {
            $notification->setContentBody($options['content']['body']);
        }

        // Set notified users
        if (isset($options['users']) && (is_array($options['users']) || $options['users'] instanceof \Traversable)) {
            foreach ($options['users'] as $user) {
                if (!in_array('Pum\Core\Extension\Notification\Entity\UserNotificationInterface', class_implements($user))) {
                    throw new \Exception('Notification: User must implement Pum\Core\Extension\Notification\Entity\UserNotificationInterface');
                }
                $notification->addUser($user);
            }
        }

        // Set notified groups
        if (isset($options['groups']) && (is_array($options['groups']) || $options['groups'] instanceof \Traversable)) {
            foreach ($options['groups'] as $group) {
                if (!in_array('Pum\Core\Extension\Notification\Entity\GroupNotificationInterface', class_implements($group))) {
                    throw new \Exception('Notification: Group must implement Pum\Core\Extension\Notification\Entity\GroupNotificationInterface');
                }
                $notification->addGroup($group);
            }
        }

        if ($notification && $notification->getEmail() && $notification->getDelayed() == null) {
            $this->send($notification);
        } else {
            $this->entityManager->transactional(function($em) use ($notification) {
                $this->entityManager->persist($notification);
                $this->entityManager->flush();
            });
        }
    }

    public function send(Notification $notification)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($notification->getContentTitle())
            ->setFrom($this->options['from'])
            ->setBody($notification->getContentBody());

        if ($notification->getGroups()->count() == 0 && $notification->getUsers()->count() == 0) {
            // Send this notification to everyone.
            $users = $this->entityManager->getRepository(UserRepository::USER_CLASS)->findAll();

            foreach ($users as $user) {
                if (filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
                    $message->addBcc($user->getEmail(), $user->getFullname());
                }
            }
        } else {
            foreach ($notification->getGroups() as $group) {
                foreach ($group->getUsers() as $user) {
                    if (filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
                        $message->addBcc($user->getEmail(), $user->getFullname());
                    }
                }
            }

            foreach ($notification->getUsers() as $user) {
                if (filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
                    $message->addBcc($user->getEmail(), $user->getFullname());
                }
            }
        }

        if (!$this->mailer->send($message)) {
            throw new \Exception('Notification: Delivery to the recipients failed');
        }
        $notification->setSent(new \DateTime());

        $this->entityManager->transactional(function($em) use ($notification) {
            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        });
    }
}
