<?php

namespace Pum\Core\Extension\Search\Result;

class FacetRows implements \IteratorAggregate
{
    private $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function getRows()
    {
        $rows = array();

        foreach ($this->rows as $row) {
            $rows[] = new FacetRow($row);
        }

        return $rows;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->getRows());
    }
}
