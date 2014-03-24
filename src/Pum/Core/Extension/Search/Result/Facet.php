<?php

namespace Pum\Core\Extension\Search\Result;

class Facet
{
    private $facet;

    public function __construct(array $facet)
    {
        $this->facet = $facet;
    }

    public function getType()
    {
        switch ($this->facet['_type']) {
            case 'range':
                return 'ranges';

            default:
                return $this->facet['_type'];
        }; 
    }

    public function getMissing()
    {
        return $this->facet['missing']; 
    }

    public function getTotal()
    {
        return $this->facet['total']; 
    }

    public function getOther()
    {
        return $this->facet['other']; 
    }

    public function count()
    {
        return count($this->facet[$this->getType()]); 
    }

    public function getRows()
    {
        $rows = array();

        if (isset($this->facet[$this->getType()])) {
            $rows = $this->facet[$this->getType()];
        }

        return new FacetRows($rows);
    }
}
