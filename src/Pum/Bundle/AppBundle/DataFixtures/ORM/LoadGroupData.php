<?php

namespace Pum\Bundle\AppBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Pum\Bundle\AppBundle\Entity\Group;

class LoadGroupData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $adminGroup = new Group('Administrators');
        $adminGroup->setAdmin(true);
        $adminGroup
            ->setPermissions(Group::getKnownPermissions())
        ;

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

        $newbieGroup = new Group('Newbies');
        $newbieGroup
            ->setPermissions(array(
                'ROLE_PA_LIST',
            ))
        ;

        $manager->persist($newbieGroup);
        $manager->persist($userGroup);
        $manager->persist($adminGroup);
        $manager->flush();

        $this->setReference('group:newbie', $newbieGroup);
        $this->setReference('group:user', $userGroup);
        $this->setReference('group:admin', $adminGroup);
    }
}
