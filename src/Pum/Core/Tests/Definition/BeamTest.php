<?php

namespace Pum\Core\Tests\Definition;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Relation;

class BeamTest extends \PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        $beam = Beam::create('jobboard')
            ->setIcon('pencil')
            ->setColor('orange')

            ->addObject(ObjectDefinition::create('jobboard_job')
                ->createField('title', 'text')
                ->createField('published_start_date', 'date')
                )
            ->addObject(ObjectDefinition::create('jobboard_application')
                ->createField('application_date', 'date')
                )

            ->addRelation(Relation::create('jobboard_job', 'applications', 'jobboard_application', 'job', Relation::ONE_TO_MANY))
        ;

        $this->assertEquals('jobboard', $name = $beam->getName());
        $this->assertEquals('pencil', $icon = $beam->getIcon());
        $this->assertEquals('orange', $color = $beam->getColor());

        $this->assertCount(2, $objects = $beam->getObjectsAsArray());
        $this->assertCount(1, $relations = $beam->getRelationsAsArray());

        $this->assertEquals(array(
            'name'      => $name,
            'icon'      => $icon,
            'color'     => $color,
            'objects'   => $objects,
            'relations' => $relations
            ), $beam->toArray());
    }

    /**
     * throw an exception when an attribute is missing (ie: ``relations``)
     * @expectedException InvalidArgumentException
     */
    public function testCreateFromArrayExceptionMissing()
    {
        $this->assertInstanceOf('Pum\Core\Definition\Beam', Beam::createFromArray(array(
            'name'      => 'name',
            'icon'      => 'icon',
            'color'     => 'color',
            'objects'   => array(),
            )));
    }

    /**
     * throw an exception when an attribute type is wrong (ie: object ``fields``)
     * @expectedException InvalidArgumentException
     */
    public function testCreateFromArrayExceptionType()
    {
        $this->assertInstanceOf('Pum\Core\Definition\Beam', Beam::createFromArray(array(
            'name'      => 'name',
            'icon'      => 'icon',
            'color'     => 'color',
            'objects'   => array(
                0 => array(
                    'name'   => 'jobboard_job',
                    'fields' => 'title'
                    )
                ),
            'relations' => array()
            )));
    }

    public function testCreateFromArray()
    {
        $this->assertInstanceOf('Pum\Core\Definition\Beam', Beam::createFromArray(array(
            'name'      => 'name',
            'icon'      => 'icon',
            'color'     => 'color',
            'objects'   => array(
                0 => array(
                    'classname' => null,
                    'name'   => 'jobboard_job',
                    'fields' => array()
                    )
                ),
            'relations' => array(
                0 => array(
                    'from' => 'from',
                    'fromName' => 'fromName',
                    'to' => 'to',
                    'toName' => '',
                    'type' => Relation::ONE_TO_MANY
                    )
                )
            )));
    }
}
