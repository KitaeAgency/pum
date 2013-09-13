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
        $adminGroup  = $manager->merge($this->getReference('group:admin'));
        $userGroup   = $manager->merge($this->getReference('group:user'));
        $newbieGroup = $manager->merge($this->getReference('group:newbie'));

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

        $newbie = new User('newbie');
        $newbie
            ->setPassword('newbie', $this->get('security.encoder_factory'))
            ->setFullname('Newbie User')
            ->addGroup($newbieGroup)
        ;

        $manager->persist($newbie);
        $manager->persist($user);
        $manager->persist($admin);
        $manager->flush();

        $this->setReference('user:admin', $admin);
        $this->setReference('user:user', $user);
        $this->setReference('user:newbie', $newbie);
    }
}
