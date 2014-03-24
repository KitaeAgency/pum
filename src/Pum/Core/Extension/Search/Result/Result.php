<?php

namespace Pum\Core\Extension\Search\Result;

class Result
{
    private $result;

    public function __construct(array $result)
    {
        $this->result = $result;
    }

    public function getRows()
    {
        $rows = array();

        if (isset($this->result['hits']['hits'])) {
            $rows = $this->result['hits']['hits'];
        }

        return new Rows($rows);
    }

    public function getFacets()
    {
        $facets = array();

        if (isset($this->result['facets'])) {
            $facets = $this->result['facets'];
        }

        return new Facets($facets);
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
}
