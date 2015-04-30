<?php

namespace Pum\Core\Tests\Definition;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Exception\DefinitionNotFoundException;
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
        } catch (DefinitionNotFoundException $e) {
        }

        $view = $object->createTableView('foo');

        $this->assertInstanceOf('Pum\Core\Definition\View\TableView', $view);
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

        $this->assertEquals('foo', $view->getColumn('foo')->getLabel());
        $this->assertEquals('tableview', $view->getColumn('foo')->getView());

        $this->assertEquals('baz', $view->getColumn('baz')->getLabel());
        $this->assertEquals('tableview', $view->getColumn('baz')->getView());
    }

    public function testSeo()
    {
        $object = new ObjectDefinition('foo');
        $object
            ->createField('foo', 'text')
            ->setSeoEnabled(true)
            ->setSeoField($object->getField('foo'))
            ->setSeoTemplate('bar')
        ;

        $array = $object->toArray();
        $this->assertEquals(true, $array['seo_enabled']);
        $this->assertEquals('foo', $array['seo_field']);
        $this->assertEquals('bar', $array['seo_template']);

        $foo = ObjectDefinition::createFromArray($array);

        $this->assertEquals(true, $foo->isSeoEnabled());
        $this->assertEquals('foo', $foo->getSeoField()->getName());
        $this->assertEquals('text', $foo->getSeoField()->getType());
        $this->assertEquals('bar', $foo->getSeoTemplate());
    }
}
