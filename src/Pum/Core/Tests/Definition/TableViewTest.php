<?php

namespace Pum\Core\Tests\Definition;

use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\TableView;

class TableViewTest extends \PHPUnit_Framework_TestCase
{
    public function testDefault()
    {
        $view = new TableView(new ObjectDefinition(), 'foo');

        $this->assertEquals('foo', $view->getName());
        $this->assertEquals(array(), $view->getColumnNames());
    }

    public function testAddColumn()
    {
        $view = new TableView(new ObjectDefinition(), 'foo');

        $view->addColumn('foo');
        $this->assertEquals(array('foo'), $view->getColumnNames());
    }

    public function testColumns()
    {
        $view = new TableView(new ObjectDefinition(), 'foo');

        $view->addColumn('foo');
        $this->assertEquals('foo', $view->getColumnField('foo'));
        $this->assertEquals('default', $view->getColumnView('foo'));

        $view->addColumn('foo', 'bar', 'baz');
        $this->assertEquals('bar', $view->getColumnField('foo'));
        $this->assertEquals('baz', $view->getColumnView('foo'));
    }

    public function testCombineValues()
    {
        $view = new TableView(new ObjectDefinition(), 'foo');

        $view->addColumn('Foo', 'foo');
        $view->addColumn('Bar', 'bar');

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
