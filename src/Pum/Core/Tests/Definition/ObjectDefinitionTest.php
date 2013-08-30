<?php

namespace Pum\Core\Tests\Definition;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Exception\TableViewNotFoundException;

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

    public function testTableViewMethods()
    {
        $object = new ObjectDefinition('foo');

        try {
            $object->getTableView('foo');
            $this->fail();
        } catch (TableViewNotFoundException $e) {
        }

        $view = $object->createTableView('foo');

        $this->assertInstanceOf('Pum\Core\Definition\TableView', $view);
        $this->assertEquals('foo', $view->getName());
        $this->assertContains($view, $object->getTableViews());
        $this->assertSame($view, $object->getTableView('foo'));
    }

    public function testCreateDefaultTableView()
    {
        $object = new ObjectDefinition('foo');
        $object
            ->createField('foo', 'text')
            ->createField('bar', 'boolean')
            ->createField('baz', 'integer')
        ;

        $view = $object->createDefaultTableView();

        $this->assertEquals(array('foo', 'bar', 'baz'), $view->getColumnNames());

        $this->assertEquals('foo', $view->getColumnField('foo'));
        $this->assertEquals('default', $view->getColumnView('foo'));

        $this->assertEquals('baz', $view->getColumnField('baz'));
        $this->assertEquals('default', $view->getColumnView('baz'));
    }
}
