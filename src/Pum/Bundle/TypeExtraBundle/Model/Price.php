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
        $this->value = $value;
        $this->currency = $currency;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function __toString()
    {
        return $this->value.' '.$this->currency;
    }
}
