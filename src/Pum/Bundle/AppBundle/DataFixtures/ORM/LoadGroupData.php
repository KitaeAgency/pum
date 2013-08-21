<?php

namespace Pum\Bundle\AppBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Pum\Bundle\AppBundle\Entity\Group;

class LoadGroupData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $adminGroup = new Group('Administrators');
        $adminGroup
            ->setPermissions(array(
                // Woodwork
                'ROLE_WW_USERS',
                'ROLE_WW_BEAMS',
                'ROLE_WW_SCHEMA',
                'ROLE_WW_PROJECTS'
            ))
        ;

        $userGroup = new Group('Users');

        $manager->persist($userGroup);
        $manager->persist($adminGroup);
        $manager->flush();

        $this->setReference('group:user', $userGroup);
        $this->setReference('group:admin', $adminGroup);
    }
}
