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

    public function createUserGroup()
    {
        $userGroup = new Group('Users');
        $userGroup
            ->setPermissions(array(
                'ROLE_PA_LIST',
                'ROLE_PA_VARS',
                'ROLE_PA_VIEW_EDIT',
                'ROLE_PA_CUSTOM_VIEWS',
                'ROLE_PA_ROUTING',
            ))
        ;
        ;

        $this->em->persist($userGroup);
        $this->em->flush();

        return $userGroup;
    }

    public function createSuperAdmin($email, $fullname, $pwd)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException(sprintf('Your email "%s" is invalid.', $email));
        }

        $user = new User($email);
        $user
            ->setFullname($fullname)
            ->setPassword($pwd, $this->encoder)
            ->setGroup($this->createSuperAdminGroup())
        ;

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function createUser($email, $fullname, $pwd, Group $group = null)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException(sprintf('Your email "%s" is invalid.', $email));
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
}
