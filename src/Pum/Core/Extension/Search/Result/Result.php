<?php

namespace Pum\Core\Extension\Search\Result;

class Result
{
    private $result;
    private $page;
    private $perPage;
    private $total;

    public function __construct(array $result, $perPage, $page)
    {
        $this->result  = $result;
        $this->perPage = $perPage;
        $this->page    = $page;
        $this->total   = isset($this->result['hits']['total']) ? $this->result['hits']['total'] : 0;
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

    public function getCurrentPage()
    {
        return $this->page;
    }

    public function getMaxPerPage()
    {
        return $this->perPage;
    }

    public function getNbPages()
    {
        return ceil($this->total/$this->perPage);
    }

    public function getTotal()
    {
        return $this->total;
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
