<?php

namespace Pum\Core\Tests\Driver;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Driver\DoctrineOrmDriver;

abstract class AbstractDriverTest extends \PHPUnit_Framework_TestCase
{
    abstract protected function getDriver();

    public function testEmpty()
    {
        $driver = $this->getDriver();

        $this->assertCount(0, $driver->getAllDefinitionNames());
    }

    public function testOneObject()
    {
        $driver = $this->getDriver();

        $def = ObjectDefinition::create('blog')
            ->createField('title', 'text')
            ->createField('subtitle', 'text')
        ;

        $driver->save($def);

        $this->assertEquals(array('blog'), $driver->getAllDefinitionNames());
    }

    public function testMultiobject()
    {
        $driver = $this->getDriver();

        $def = ObjectDefinition::create('blog')
            ->createField('title', 'text')
            ->createField('subtitle', 'text')
        ;

        $driver->save($def);

        $def = ObjectDefinition::create('post')
            ->createField('title', 'text')
            ->createField('content', 'text')
        ;

        $driver->save($def);

        $this->assertEquals(array('blog', 'post'), $driver->getAllDefinitionNames());
    }
}
