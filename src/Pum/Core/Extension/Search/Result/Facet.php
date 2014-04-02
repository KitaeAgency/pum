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

            case 'date_histogram':
            case 'histogram':
                return 'entries';

            default:
                return $this->facet['_type'];
        }; 
    }

    public function getMissing()
    {
        return $this->facet['missing']; 
    }

    public function getTotal($default = 0)
    {
        if (isset($this->facet['total'])) { 
            return $this->facet['total'];
        }

        if ($this->getType() === 'entries') {
            $count = 0;
            foreach ($this->facet['entries'] as $v) {
                $count += $v['count'];
            }

            return $count;
        }

        return $default;
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
