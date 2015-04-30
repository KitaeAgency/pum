<?php

namespace Pum\Bundle\AppBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Pum\Bundle\AppBundle\Entity\User;

class LoadUserData extends Fixture
{
    public function getOrder()
    {
        return 3; // depends on group
    }

    public function load(ObjectManager $manager)
    {
        // Use console command to create first admin
    }
}
