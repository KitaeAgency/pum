<?php

namespace Pum\Core\Extension\Search\Result;

class Facets implements \IteratorAggregate
{
    private $facets;

    public function __construct(array $facets)
    {
        $this->facets = $facets;
    }

    public function getFacets()
    {
        $facets = array();

        foreach ($this->facets as $name => $facet) {
            $facets[$name] = new Facet($facet);
        }

        return $facets;
    }

    public function getFacet($name)
    {
        foreach ($this->facets as $facetName => $facet) {
            if ($name == $facetName) {
                return new Facet($facet);
            }
        }

        return null;
    }

    public function count()
    {
        return count($this->facets); 
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->getFacets());
    }
}
