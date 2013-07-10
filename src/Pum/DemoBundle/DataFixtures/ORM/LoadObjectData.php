<?php

namespace Pum\DemoBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Pum\DemoBundle\Entity\Object;

class LoadObjectData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist(Object::create('blog')
            ->createField('name', 'text')
            ->createField('copyright', 'text')
        );

        $manager->persist(Object::create('blog_post')
            ->createField('title', 'text')
            ->createField('content', 'longtext')
        );

        $manager->flush();

        $manager->getRepository('Pum\Object\Foo');
    }
}
