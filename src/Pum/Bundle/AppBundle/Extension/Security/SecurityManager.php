<?php

namespace Pum\Bundle\AppBundle\Extension\Security;

use Doctrine\ORM\EntityManager;
use Pum\Bundle\AppBundle\Entity\User;
use Pum\Bundle\AppBundle\Entity\Group;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class SecurityManager
{
    /**
     * @var EntityManager 
     */
    protected $em;

    /**
     * @var EncoderFactory 
     */
    protected $encoder;

    public function __construct(EntityManager $entityManager, EncoderFactory $encoder)
    {
        $this->em      = $entityManager;
        $this->encoder = $encoder;
    }

    public function getSuperAdminGroup()
    {
        return $this->em->getRepository('Pum\Bundle\AppBundle\Entity\Group')->getAdminGroup();
    }

    public function createGroup($name, array $permissions)
    {
        $group = new Group($name);
        $group->setPermissions($permissions);

        $this->em->persist($group);
        $this->em->flush();

        return $group;
    }

    public function createSuperAdminGroup($name = 'Super administrators')
    {
        $superAdminGroup = new Group($name);
        $superAdminGroup
            ->setPermissions(Group::getKnownPermissions())
            ->setAdmin(true)
        ;

        $this->em->persist($superAdminGroup);
        $this->em->flush();

        return $superAdminGroup;
    }

    public function getUser($username)
    {
        return $this->em->getRepository('Pum\Bundle\AppBundle\Entity\User')->findOneBy(array('username' => $username));
    }

    public function createUser($email, $fullname, $pwd, Group $group = null)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException(sprintf('Your email "%s" is invalid.', $email));
        }

        if (null !== $this->getUser($email)) {
            throw new \RuntimeException(sprintf('Your email "%s" is already used.', $email));
        }

        $user = new User($email);
        $user
            ->setFullname($fullname)
            ->setPassword($pwd, $this->encoder)
        ;

        if (null !== $group) {
            $user->setGroup($group);
        }

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function createSuperAdmin($email, $fullname, $pwd)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException(sprintf('Your email "%s" is invalid.', $email));
        }

        if (null !== $this->getUser($email)) {
            throw new \RuntimeException(sprintf('Your email "%s" is already used.', $email));
        }

        if (null === $superAdminGroup = $this->getSuperAdminGroup()) {
            $superAdminGroup = $this->createSuperAdminGroup();
        }

        $user = new User($email);
        $user
            ->setFullname($fullname)
            ->setPassword($pwd, $this->encoder)
            ->setGroup($superAdminGroup)
        ;

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
