<?php

namespace Pum\Core\Extension\Search\Result;

class Rows implements \IteratorAggregate
{
    private $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function getRows()
    {
        $rows = array();

        foreach ($this->rows as $hit) {
            // Overall search could have same id
            //$rows[$hit['_id']] = new Row($hit);

            $rows[] = new Row($hit);
        }

        return $rows;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->getRows());
    }
}
