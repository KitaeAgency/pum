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
        $priceData = explode(" ", $string);
        if (count($priceData) !== 2 || (!is_numeric($priceData[0]) || !$priceData[1])) {
            throw new Exception('Please provide a valid price string : "value currency"');
        }

        return new self($priceData[0], $priceData[1]);
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
    public function add(Price $price)
    {
        if ($price->getCurrency() !== $this->getCurrency()) {
            throw new Exception("Prices must be in the same currency");
        }

        $maxScale = $this->getMaxScale($this->getValue(), $price->getValue());

        return new self(bcadd($this->getValue(), $price->getValue(), $maxScale), $this->getCurrency());
    }

    /**
     * @return Price
     */
    public function substract(Price $price)
    {
        if ($price->getCurrency() !== $this->getCurrency()) {
            throw new Exception("Prices must be in the same currency");
        }

        $maxScale = $this->getMaxScale($this->getValue(), $price->getValue());

        return new self(bcsub($this->getValue(), $price->getValue(), $maxScale), $this->getCurrency());
    }

    /**
     * @return Price
     */
    public function multiply($coef)
    {
        $maxScale = $this->getMaxScale($this->getValue(), $coef);

        return new self(bcmul($this->getValue(), $coef, $maxScale), $this->getCurrency());
    }

    /**
     * $distributeArray is an array of integer
     * @return Array of Price with the adjustment price applied on the last element of $distributeArray
     */
    public function distribute(array $distributeArray, $decimal = 2)
    {
        if (count($distributeArray) === 0) {
            throw new Exception("distributeArray must not be empty");
        }

        $distributeResult = array();
        $totalCoef        = array_sum($distributeArray);
        $scale            = $decimal + 1;

        foreach ($distributeArray as $coef) {
            $distributeResult[] = new self(round(bcmul(bcdiv($coef, $totalCoef, $scale), $this->getValue(), $scale), $decimal, PHP_ROUND_HALF_DOWN), $this->getCurrency());
        }
        array_pop($distributeResult);

        $sumResult = new self(0, $this->getCurrency());
        foreach ($distributeResult as $price) {
            $sumResult = $sumResult->add($price);
        }
        $distributeResult[] = $this->substract($sumResult);
        
        return $distributeResult;
    }

    /**
     * Get Scale from a number
     */
    public function getScale($number)
    {
        $pricePart = explode(".", (string)$number);
        if (count($pricePart) === 2)
        {
            return strlen(rtrim($pricePart[1], "0"));
        }

        return 0;
    }

    /**
     * @return max scale from 2 prices
     */
    public function getMaxScale($numberA, $numberB)
    {
        return max($this->getScale($numberA), $this->getScale($numberB));
    }
}
