<?php

namespace Pum\DemoBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Pum\Core\Definition\ObjectDefinition;

class LoadObjectData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist($blog = ObjectDefinition::create('blog')
            ->createField('name', 'text')
            ->createField('copyright', 'text')
        );

        $manager->persist($post = Object::create('blog_post')
            ->createField('title', 'text')
            ->createField('content', 'longtext')
        );

        $manager->flush();
    }
}
