<?php

namespace Pum\Core\Tests;
use Doctrine\DBAL\DriverManager;
use Pum\Core\Config;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\Driver\StaticDriver;
use Pum\Core\Extension\EmFactory\EmFactoryExtension;
use Pum\Core\SchemaManager;
use Pum\Core\Type\Factory\StaticTypeFactory;
use Pum\Core\Type\TextType;

class SchemaManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testHelloWorld()
    {
        // definition
        $sm = $this->createSchemaManager();
        $sm->getConfig()->addExtension($emFactory = new EmFactoryExtension($this->createConnection()));
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

    private function createSchemaManager()
    {
        $driver      = new StaticDriver();
        $typeFactory = new StaticTypeFactory(array(
            'text' => new TextType()
        ));

        return new SchemaManager(new Config($driver, $typeFactory));
    }

    private function createConnection()
    {
        // delete file at the end of test
        $file   = tempnam(sys_get_temp_dir(), 'pum_');
        register_shutdown_function(function () use ($file) {
            unlink($file);
        });

        return DriverManager::getConnection(array(
            'driver' => 'pdo_sqlite',
            'path'   => $file,
        ));

    }
}
