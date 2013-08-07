<?php

namespace Pum\Bundle\TypeExtraBundle\Model;

/**
 * The value of a price object should NEVER change. You should instead use
 * new objects.
 */
class Price
{
    protected $value;
    protected $currency;

    public function __construct($value, $currency)
    {
        $this->value    = trim($value);
        $this->currency = trim($currency);
    }

    /**
     * @see self::__construct
     *
     * @return Price
     */
    static public function createFromString($string)
    {
        return new self($value, $currency);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value.' '.$this->currency;
    }

    /**
     * @return Price
     */
    public function add(Price $priceA, Price $priceB)
    {
        if ($priceA->getCurrency() !== $priceB->getCurrency()) {
            throw new Exception("Prices must be in the same currency");
        }

        $maxScale = $this->getMaxScale($priceA, $PriceB);

        return new Price(bcadd($priceA->getValue(), $priceB->getValue(), $maxScale), $this->getCurrency());
    }

    /**
     * @return Price
     */
    public function substract(Price $priceA, Price $priceB)
    {
        if ($priceA->getCurrency() !== $priceB->getCurrency()) {
            throw new Exception("Prices must be in the same currency");
        }

        $maxScale = $this->getMaxScale($priceA, $PriceB);

        return new Price(bcsub($priceA->getValue(), $priceB->getValue(), $maxScale), $this->getCurrency());
    }

    /**
     * @return Price
     */
    public function multiply(Price $priceA, Price $priceB)
    {
        if ($priceA->getCurrency() !== $priceB->getCurrency()) {
            throw new Exception("Prices must be in the same currency");
        }

        $maxScale = $this->getMaxScale($priceA, $PriceB);

        return new Price(bcmul($priceA->getValue(), $priceB->getValue(), $maxScale), $this->getCurrency());
    }

    /**
     * @return Price
     */
    public function distribute(Price $priceA, Price $priceB)
    {
        
    }

    /**
     * Get Scale from a number
     */
    public function getScale($number)
    {
        $pricePart = explode(".", $number);
        if (count($pricePart) === 2)
        {
            return strlen(rtrim($pricePart[1], "0"));
        }

        return 0;
    }

    /**
     * @return max scale from 2 prices
     */
    public function getMaxScale(Price $priceA, Price $priceB)
    {
        return max($this->getScale($priceA->getCurrency()), $this->getScale($priceB->getCurrency()));
    }
}
