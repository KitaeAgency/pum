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

    public function count()
    {
        return isset($this->result['hits']['total']) ? $this->result['hits']['total'] : 0;
    }

    public function time()
    {
        return isset($this->result['took']) ? $this->result['took'] : 0;
    }

    public function isTimeout()
    {
        return isset($this->result['timed_out']) ? $this->result['timed_out'] : false;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->getRows());
    }
}
