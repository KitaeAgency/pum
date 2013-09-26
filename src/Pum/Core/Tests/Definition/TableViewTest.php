<?php

namespace Pum\Core\Tests\Definition;

use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\View\TableView;

class TableViewTest extends \PHPUnit_Framework_TestCase
{
    public function testDefault()
    {
        $view = new TableView(new ObjectDefinition(), 'foo');

        $this->assertEquals('foo', $view->getName());
        $this->assertEmpty($view->getColumns()->toArray());
    }

    public function testAddColumn()
    {
        $view = new TableView($def = new ObjectDefinition(), 'foo');
        $def->createField('foo', 'text');

        $view->createColumn('foo');
        $this->assertCount(1, $view->getColumns());
    }

    public function testColumns()
    {
        $object = new ObjectDefinition();
        $field = $object->createField('foo','text')->getField('foo');
        $view = new TableView($object, 'foo');

        $view->createColumn('foo');
        $this->assertEquals('foo', $view->getColumn('foo')->getLabel());
        $this->assertEquals('default', $view->getColumn('foo')->getView());

        $view->createColumn('foo2', $field, 'baz');
        $this->assertEquals('foo2', $view->getColumn('foo2')->getLabel());
        $this->assertEquals('baz', $view->getColumn('foo2')->getView());
    }

    public function testCombineValues()
    {
        $obj = new ObjectDefinition();
        $fieldA = $obj->createField('foo', 'text')->getField('foo');
        $fieldB = $obj->createField('bar', 'text')->getField('bar');

        $view = new TableView($obj, 'foo');

        $view->createColumn('Foo', $fieldA);
        $view->createColumn('Bar', $fieldB);

        // empty array
        $val = $view->combineValues(array());
        $this->assertEquals(array(), $val);

        // correct array
        $val = $view->combineValues(array('a', 'b'));
        $this->assertEquals(array('Foo' => 'a', 'Bar' => 'b'), $val);

        // extra column
        try {
            $view->combineValues(array('a', 'b', 'c'));
            $this->fail();
        } catch (\Exception $e) {
        }

        // missing column
        $val = $view->combineValues(array('a'));
        $this->assertEquals(array('Foo' => 'a'), $val);

        // direct number-access
        $val = $view->combineValues(array(1 => 'a'));
        $this->assertEquals(array('Bar' => 'a'), $val);
    }
}
