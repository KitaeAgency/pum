<?php

namespace Pum\Bundle\WoodworkBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserRepository extends EntityRepository implements UserProviderInterface
{
    const USER_CLASS = 'Pum\Bundle\WoodworkBundle\Entity\User';

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->findOneBy(array('username' => $username));

        if (!$user) {
            $e = new UsernameNotFoundException(sprintf('No user with username "%s".', $username));
            $e->setUsername($username);

            throw $e;
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === self::USER_CLASS || is_subclass_of($class, self::USER_CLASS);
    }
}
