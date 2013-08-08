<?php

namespace Pum\Bundle\WoodworkBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserRepository extends EntityRepository implements UserProviderInterface
{
    const USER_CLASS = 'Pum\Bundle\WoodworkBundle\Entity\User';

    public function getPage($page = 1)
    {
        $page = max(1, (int) $page);

        $pager = new Pagerfanta(new DoctrineORMAdapter($this->createQueryBuilder('u')->orderBy('u.username', 'ASC')));
        $pager->setCurrentPage($page);

        return $pager;
    }

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

    public function save(User $user)
    {
        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();
    }

    public function delete(User $user)
    {
        $em = $this->getEntityManager();
        $em->remove($user);
        $em->flush();
    }
}
