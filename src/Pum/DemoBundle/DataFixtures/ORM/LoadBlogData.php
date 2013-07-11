<?php

namespace Pum\DemoBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;

class LoadBlogData extends Fixture
{
    public function getOrder()
    {
        return 2;
    }

    public function load(ObjectManager $manager)
    {
        $manager = $this->get('pum');

        $blog = $manager->createObject('blog');

        $blog->name      = 'Premier blog';
        $blog->copyright = '2013 - Tous droits réservés';

        $manager->persist($blog);
        $manager->flush();
    }
}
