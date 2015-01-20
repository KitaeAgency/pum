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

    public function createSuperAdminGroup()
    {
        $superAdminGroup = new Group('Super administrators');
        $superAdminGroup
            ->setPermissions(Group::getKnownPermissions())
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

    public function addGroupToUser(User $user, Group $group)
    {
        $user->addGroup($group);
        $this->em->flush();

        return $this;
    }

    public function createUser($email, $fullname, $pwd, Group $group = null)
    {
        $user = new User($email);
        $user
            ->setFullname($fullname)
            ->setPassword($pwd, $this->encoder)
        ;

        if (null !== $group) {
            $user->addGroup($group);
        }

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
