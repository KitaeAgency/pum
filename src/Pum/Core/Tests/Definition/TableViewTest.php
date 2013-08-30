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
}
