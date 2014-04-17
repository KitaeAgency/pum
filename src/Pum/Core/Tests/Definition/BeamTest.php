<?php

namespace Pum\Core\Tests\Definition;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;

class BeamTest extends \PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        $beam = Beam::create('jobboard')
            ->setIcon('pencil2')
            ->setColor('orange')

            ->addObject(ObjectDefinition::create('jobboard_job')
                ->createField('title', 'text')
                ->createField('published_start_date', 'date')
                )
            ->addObject(ObjectDefinition::create('jobboard_application')
                ->createField('application_date', 'date')
                )
        ;

        $this->assertEquals('jobboard', $name = $beam->getName());
        $this->assertEquals('pencil2', $icon = $beam->getIcon());
        $this->assertEquals('orange', $color = $beam->getColor());

        $this->assertCount(2, $objects = $beam->getObjectsAsArray());

        $this->assertEquals(array(
            'name'      => $name,
            'icon'      => $icon,
            'color'     => $color,
            'objects'   => $objects
            ), $beam->toArray());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateFromArrayExceptionMissingObjects()
    {
        $this->assertInstanceOf('Pum\Core\Definition\Beam', Beam::createFromArray(array(
            'name'      => 'name',
            'icon'      => 'icon',
            'color'     => 'color',
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
                )
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
                )
            )));
    }

    public function testSignaturesShouldBeValid()
    {
        $beam = Beam::createFromArray(array(
            'name'      => 'name',
            'icon'      => 'icon',
            'color'     => 'color',
            'objects'   => array(
                0 => array(
                    'classname' => null,
                    'name'   => 'jobboard_job',
                    'fields' => array()
                )
            )
        ));

        $baseSignature = $beam->getSignature();
        $beam->setName('updated-name');
        $updatedSignature = $beam->getSignature();
        $beam->setName('name');
        $backToOriginalSignature = $beam->getSignature();

        $this->assertNotEquals($baseSignature, $updatedSignature, "Signatures should be different");
        $this->assertEquals($baseSignature, $backToOriginalSignature, "Signatures should be equals");
    }
}
