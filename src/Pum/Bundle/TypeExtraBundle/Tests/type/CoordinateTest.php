<?php

namespace Pum\Bundle\TypeExtraBundle\Tests\Type;
use Pum\Bundle\TypeExtraBundle\Model\Coordinate;

class CoordinateTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $coordinate = new Coordinate('48.853', '2.35');
        $this->assertEquals('48.853', $coordinate->getLat());
        $this->assertEquals('2.35', $coordinate->getLng());

        $coordinate = Coordinate::createFromString('48.853,2.35');
        $this->assertEquals('48.853', $coordinate->getLat());
        $this->assertEquals('2.35', $coordinate->getLng());

        $coordinate = Coordinate::createFromString('48.853,2.35')
            ->setLat('25.39')
            ->setLng('7.246')
        ;
        $this->assertEquals('25.39', $coordinate->getLat());
        $this->assertEquals('7.246', $coordinate->getLng());

        $coordinate = new Coordinate('48.853', '2.35');
        $this->assertEquals('48.853,2.35', $coordinate);
    }
}
