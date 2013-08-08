<?php

namespace Pum\Bundle\WoodworkBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Pum\Bundle\WoodworkBundle\Entity\User;

class LoadUserData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $admin = new User('admin');
        $admin
            ->setPassword('admin', $this->get('security.encoder_factory'))
            ->setFullname('The Administrator')
        ;

        $user = new User('user');
        $user
            ->setPassword('user', $this->get('security.encoder_factory'))
            ->setFullname('Regular User')
        ;

        for ($i = 1; $i <= 100; $i++) {
            $dummy = new User('dummy'.$i);
            $dummy
                ->setPassword('dummy'.$i, $this->get('security.encoder_factory'))
                ->setFullname('Dummy'.$i)
            ;
            $manager->persist($dummy);
        }

        $manager->persist($user);
        $manager->persist($admin);
        $manager->flush();

        $this->setReference('ww_user:admin', $admin);
        $this->setReference('ww_user:user',  $user);
    }
}
