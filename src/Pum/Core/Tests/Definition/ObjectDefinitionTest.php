<?php

namespace Pum\Core\Tests\Definition;
use Pum\Core\Definition\ObjectDefinition;

class ObjectDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $object = new ObjectDefinition();
        $this->assertNull($object->getName());

        $object = new ObjectDefinition('foo');
        $this->assertEquals('foo', $object->getName());

        $object = ObjectDefinition::create('foo');
        $this->assertEquals('foo', $object->getName());

        $object = ObjectDefinition::create()
            ->setName('foo')
            ->createField('bar', 'baz')
            ->createField('baz', 'bar')
        ;

        $this->assertCount(2, $object->getFields());
        $this->assertEquals('bar', $object->getFields()->get(0)->getName());
        $this->assertEquals('baz', $object->getFields()->get(0)->getType());
        $this->assertEquals('baz', $object->getFields()->get(1)->getName());
        $this->assertEquals('bar', $object->getFields()->get(1)->getType());
    }
}
