<?php

namespace Pum\Bundle\WoodworkBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Pum\Bundle\WoodworkBundle\Entity\Group;

class LoadGroupData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $adminGroup = new Group('Administrators');
        $adminGroup
            ->setPermissions(array('ROLE_WW_USERS', 'ROLE_WW_BEAMS', 'ROLE_WW_SCHEMA', 'ROLE_WW_PROJECTS'))
        ;

        $userGroup = new Group('Users');

        $manager->persist($userGroup);
        $manager->persist($adminGroup);
        $manager->flush();

        $this->setReference('ww_group:user', $userGroup);
        $this->setReference('ww_group:admin', $adminGroup);
    }
}
