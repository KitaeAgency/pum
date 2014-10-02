<?php

namespace Pum\Core\Tests\Extension\EmFactory;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\Driver\DoctrineOrmDriver;
use Pum\Core\Tests\Driver\DoctrineOrmDriverTest;
use Pum\Core\Tests\ObjectFactoryTest;
use Pum\Core\Type\Factory\StaticTypeFactory;
use Pum\Core\Type\TextType;
use Pum\Core\Extension\EmFactory\EmFactory;
use Pum\Core\Extension\EmFactory\Listener\SchemaUpdateListener;

class EmFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testEmFactory()
    {
        // definition
        $objectFactory = ObjectFactoryTest::createObjectFactory();
        $emFactory     = new EmFactory(ObjectFactoryTest::createConnection());
        $objectFactory->getEventDispatcher()->addSubscriber(new SchemaUpdateListener($emFactory));

        $objectFactory->saveBeam($blogBeam = Beam::create('beam_blog')
            ->addObject(ObjectDefinition::create('blog')
                ->createField('title', 'text')
                ->createfield('content', 'text')
            )
        );
        $objectFactory->saveProject(Project::create('project_A')
            ->addBeam($blogBeam)
        );

        $em = $emFactory->getManager($objectFactory, 'project_A');
        $em->updateSchema();
        $em->clearCache();

        $blog = $em->createObject('blog');

        $blog->setTitle('Foo');
        $blog->setContent('bar');

        $em->persist($blog);
        $em->flush();
    }

    public static function createEmFactory()
    {
        return new EmFactoryExtension(ObjectFactoryTest::createConnection());
    }
}
