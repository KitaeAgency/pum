<?php

namespace Pum\Core\Extension\Log\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Pum\Core\Events;
use Pum\Core\Extension\EmFactory\EmFactory;
use Pum\Core\Extension\Log\Service\LogService;
use Pum\Core\Event\ObjectEvent;
use Pum\Bundle\CoreBundle\Entity\Log;

class PumListener implements EventSubscriberInterface
{
    /**
     * @var Doctrine\ORM\EntityManager $em
     */
    protected $em = null;

    /**
     * @var Pum\Core\Extension\Log\Service\LogService $logService
     */
    protected $emFactory;

    /**
     * @var Pum\Core\Extension\Log\Service\LogService $logService
     */
    protected $logService;

    public static function getSubscribedEvents()
    {
        return array(
            Events::OBJECT_PRE_CREATE => 'onCreate',
            Events::OBJECT_UPDATE => 'onUpdate',
            Events::OBJECT_DELETE => 'onDelete',
            Events::POST_FLUSH => 'postFlush',
        );
    }

    public function __construct(EmFactory $emFactory, LogService $logService, EntityManager $em)
    {
        $this->logService = $logService;
        $this->emFactory = $emFactory;
        $this->em = $em;

        $this->logs = array();
    }

    public function onCreate(ObjectEvent $event)
    {
        if ($this->logService->getEnabled()) {
            $object = $event->getObject();
            $objectFactory = $event->getObjectFactory();

            $em = $this->emFactory->getManager($objectFactory, $object::PUM_PROJECT);
            if (($log = $this->logService->create(
                $object,
                Log::EVENT_CREATE,
                array('em' => $em)
            ))) {
                $this->logs[] = $log;
            }
        }
    }

    public function onUpdate(ObjectEvent $event)
    {
        if ($this->logService->getEnabled()) {
            $object = $event->getObject();
            $objectFactory = $event->getObjectFactory();

            $em = $this->emFactory->getManager($objectFactory, $object::PUM_PROJECT);
            if ($this->logService->getEnabled() && ($log = $this->logService->create(
                $object,
                Log::EVENT_UPDATE,
                array('em' => $em)
            ))) {
                $this->logs[] = $log;
            }
        }
    }

    public function onDelete(ObjectEvent $event)
    {
        if ($this->logService->getEnabled()) {
            $object = $event->getObject();
            $objectFactory = $event->getObjectFactory();

            if ($this->logService->getEnabled() && ($log = $this->logService->create(
                $object,
                Log::EVENT_DELETE
            ))) {
                $this->logs[] = $log;
            }
        }
    }

    public function postFlush()
    {
        if (!empty($this->logs)) {
            $this->em->transactional(function($em) {
                foreach ($this->logs as $log) {
                    $em->persist($log);
                }
                $em->flush();
            });
        }
    }
}
