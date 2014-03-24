<?php

namespace Pum\Core\Extension\Search\Result;

class Term
{
    private $term;

    public function __construct(array $term)
    {
        $this->term = $term;
    }

    public function getName($default = null)
    {
        return isset($this->term['term']) ? $this->term['term'] : $default;
    }

    public function getCount()
    {
        return isset($this->term['count']) ? $this->term['count'] : 0;
    }

}
