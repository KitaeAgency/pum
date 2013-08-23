<?php

namespace Pum\Bundle\TypeExtraBundle\Tests\Type;
use Pum\Bundle\TypeExtraBundle\Model\Price;

class PriceTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $price = new Price('50', 'EUR');
        $this->assertEquals('50', $price->getValue());
        $this->assertEquals('EUR', $price->getCurrency());
    }

    public function testString()
    {
        $price = Price::createFromString('50 EUR');
        $this->assertEquals('50', $price->getValue());
        $this->assertEquals('EUR', $price->getCurrency());
        $this->assertEquals('50 EUR', $price);
    }

    public function testAdd()
    {
        $priceA = Price::createFromString('30 EUR');
        $priceB = Price::createFromString('20 EUR');

        $priceC = $priceA->add($priceB);
        $this->assertEquals('50 EUR', $priceC);

        $priceC = $priceA->substract($priceB);
        $this->assertEquals('10 EUR', $priceC);

        $priceC = $priceB->multiply(5);
        $this->assertEquals('100 EUR', $priceC);
    }

    public function testDistribute()
    {

        // 30 € in 2 different parts
        $price = Price::createFromString('30 EUR');
        list($a, $b) = $price->distribute(array(2, 1));

        $this->assertEquals('20 EUR', $a);
        $this->assertEquals('10 EUR', $b);

        // 1€ in 3 parts
        $price = Price::createFromString('1 EUR');
        list($a, $b, $c) = $price->distribute(array(1, 1, 1));

        $this->assertEquals('0.33 EUR', $a);
        $this->assertEquals('0.33 EUR', $b);
        $this->assertEquals('0.34 EUR', $c);
    }
}
