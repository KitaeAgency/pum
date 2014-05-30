<?php

namespace Pum\Bundle\AppBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Pum\Bundle\AppBundle\Entity\Permission;

class LoadPermissionData extends Fixture
{
    public function getOrder()
    {
        return 3; //depend on project
    }

    public function load(ObjectManager $manager)
    {
        $adminGroup      = $manager->merge($this->getReference('group:admin'));
        $frenchProject   = $manager->merge($this->getReference('project:french-team'));
        $englishProject  = $manager->merge($this->getReference('project:english-team'));

        $permission1 = new Permission();
        $permission1
            ->setGroup($adminGroup)
            ->setAttribute('PUM_OBJ_MASTER')
            ->setProject($frenchProject)
        ;
        $adminGroup->addAdvancedPermission($permission1);

        $permission2 = new Permission();
        $permission2
            ->setGroup($adminGroup)
            ->setAttribute('PUM_OBJ_MASTER')
            ->setProject($englishProject)
        ;
        $adminGroup->addAdvancedPermission($permission2);

        $manager->persist($permission1);
        $manager->persist($permission2);
        $manager->persist($adminGroup);
        $manager->flush();
    }
}
