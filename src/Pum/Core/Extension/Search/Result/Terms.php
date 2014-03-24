<?php

namespace Pum\Core\Extension\Search\Result;

class Terms implements \IteratorAggregate
{
    private $terms;

    public function __construct(array $terms)
    {
        $this->terms = $terms;
    }

    public function getTerms()
    {
        $terms = array();

        foreach ($this->terms as $term) {
            $terms[] = new Term($term);
        }

        return $terms;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->getTerms());
    }
}
