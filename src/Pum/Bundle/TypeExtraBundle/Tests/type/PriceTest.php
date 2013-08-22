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

        $coordinate = Price::createFromString('50 EUR');
        $this->assertEquals('50', $price->getValue());
        $this->assertEquals('EUR', $price->getCurrency());
        $this->assertEquals('50 EUR', $price);

        $priceA = Price::createFromString('30 EUR');
        $priceB = Price::createFromString('20 EUR');

        $priceC = $priceA->add($priceB);
        $this->assertEquals('50', $priceC->getValue());
        $this->assertEquals('EUR', $priceC->getCurrency());

        $priceC = $priceA->substract($priceB);
        $this->assertEquals('10', $priceC->getValue());
        $this->assertEquals('EUR', $priceC->getCurrency());

        $priceC = $priceB->multiply(5);
        $this->assertEquals('100', $priceC->getValue());
        $this->assertEquals('EUR', $priceC->getCurrency());

        list($priceC, $priceD) = $priceA->distribute(array(2, 1));
        $this->assertEquals('20', $priceC->getValue());
        $this->assertEquals('EUR', $priceC->getCurrency());
        $this->assertEquals('10', $priceD->getValue());
        $this->assertEquals('EUR', $priceD->getCurrency());
    }
}
