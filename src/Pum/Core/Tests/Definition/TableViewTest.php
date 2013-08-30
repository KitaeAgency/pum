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
}
