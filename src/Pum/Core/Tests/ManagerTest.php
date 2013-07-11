<?php

namespace Pum\Core\Tests;
use Doctrine\DBAL\DriverManager;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Driver\StaticDriver;
use Pum\Core\Manager;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testHelloWorld()
    {
        $manager = $this->createManager();

        $manager->saveDefinition(ObjectDefinition::create('blog')
            ->createField('title', 'text')
            ->createfield('content', 'text')
        );

        $blog = $manager->createObject('blog');

        $blog->set('title', 'Foo');
        $blog->set('content', 'Bar');

        $manager->persist($blog);
        $manager->flush();
    }

    private function createManager()
    {
        // delete file at the end of test
        $file   = tempnam(sys_get_temp_dir(), 'pum_');
        register_shutdown_function(function () use ($file) {
            unlink($file);
        });

        $conn = DriverManager::getConnection(array(
            'driver' => 'pdo_sqlite',
            'path'   => $file,
        ));

        return new Manager(new StaticDriver(), $conn);
    }
}
