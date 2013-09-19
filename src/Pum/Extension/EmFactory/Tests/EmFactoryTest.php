<?php

namespace Pum\Core\Tests\Extension\EmFactory;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\Driver\DoctrineOrmDriver;
use Pum\Extension\EmFactory\EmFactoryExtension;
use Pum\Core\Tests\Driver\DoctrineOrmDriverTest;
use Pum\Core\Tests\SchemaManagerTest;
use Pum\Core\Type\Factory\StaticTypeFactory;
use Pum\Core\Type\TextType;

class EmFactoryExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testEmFactory()
    {
        // definition
        $sm = SchemaManagerTest::createSchemaManager();
        $sm->addExtension($emFactory = self::createEmFactory());
        $sm->saveBeam($blogBeam = Beam::create('beam_blog')
            ->addObject(ObjectDefinition::create('blog')
                ->createField('title', 'text')
                ->createfield('content', 'text')
            )
        );
        $sm->saveProject(Project::create('project_A')
            ->addBeam($blogBeam)
        );

        $em = $emFactory->getManager('project_A');

        $blog = $em->createObject('blog');

        $blog->set('title', 'Foo');
        $blog->set('content', 'Bar');

        $em->persist($blog);
        $em->flush();
    }

    public static function createEmFactory()
    {
        return new EmFactoryExtension(SchemaManagerTest::createConnection());
    }
}
