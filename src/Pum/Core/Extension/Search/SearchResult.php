<?php

namespace Pum\Core\Extension\Search;

class SearchResult implements \IteratorAggregate
{
    private $result;

    public function __construct(array $result)
    {
        $this->result = $result;
    }

    public function getRows()
    {
        $rows = array();

        foreach ($this->result['hits']['hits'] as $hit) {
            $rows[] = new SearchResultRow($hit);
        }

        return $rows;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->getRows());
    }
}
