<?php

namespace Pum\Bundle\WoodworkBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Pum\Bundle\WoodworkBundle\Entity\User;

class LoadUserData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $adminGroup = $manager->merge($this->getReference('ww_group:admin'));
        $userGroup  = $manager->merge($this->getReference('ww_group:user'));

        $admin = new User('admin');
        $admin
            ->setPassword('admin', $this->get('security.encoder_factory'))
            ->setFullname('The Administrator')
            ->addGroup($adminGroup)
        ;

        $user = new User('user');
        $user
            ->setPassword('user', $this->get('security.encoder_factory'))
            ->setFullname('Regular User')
            ->addGroup($userGroup)
        ;

        $manager->persist($user);
        $manager->persist($admin);
        $manager->flush();

        $this->setReference('ww_user:admin', $admin);
        $this->setReference('ww_user:user',  $user);
    }

    public function getOrder()
    {
        return 2;
    }
}
