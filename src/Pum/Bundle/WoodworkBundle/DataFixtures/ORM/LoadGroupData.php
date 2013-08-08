<?php

namespace Pum\Bundle\WoodworkBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Pum\Bundle\WoodworkBundle\Entity\Group;

class LoadGroupData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $admin = $manager->merge($this->getReference('ww_user:admin'));
        $user = $manager->merge($this->getReference('ww_user:user'));

        $adminGroup = new Group('Administrators');
        $adminGroup
            ->setPermissions(array('ROLE_WW_USERS', 'ROLE_WW_BEAMS', 'ROLE_WW_SCHEMA', 'ROLE_WW_PROJECTS'))
            ->addUser($admin)
        ;

        $userGroup = new Group('Users');
        $userGroup
            ->addUser($user)
        ;

        $manager->persist($userGroup);
        $manager->persist($adminGroup);
        $manager->flush();

        $this->setReference('ww_group:user', $userGroup);
        $this->setReference('ww_group:admin', $adminGroup);
    }

    public function getOrder()
    {
        return 2;
    }
}
