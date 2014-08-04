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
                //App
                'ROLE_APP_CONFIG',

                // Woodwork
                'ROLE_WW_USERS',
                'ROLE_WW_BEAMS',
                'ROLE_WW_LOGS',
                'ROLE_WW_PROJECTS',

                // Project Admin
                'ROLE_PA_LIST',
                'ROLE_PA_EDIT',
                'ROLE_PA_DELETE',

                'ROLE_PA_VIEW_EDIT',
                'ROLE_PA_DEFAULT_VIEWS',
            ))
        ;

        $userGroup = new Group('Users');
        $userGroup
            ->setPermissions(array(
                'ROLE_PA_LIST',
                'ROLE_PA_EDIT',
                'ROLE_PA_DELETE',

                'ROLE_PA_VIEW_EDIT',
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
