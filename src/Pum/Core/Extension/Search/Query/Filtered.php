<?php

namespace Pum\Core\Extension\Search\Query;

class Filtered extends Query
{
    const QUERY_KEY = 'filtered';

    private $query;
    private $filter;

    public function __construct($query = null, $filter = null)
    {
        $this->query = $query;
        $this->filter = $filter;
    }

    public function setQuery(Query $query)
    {
        $this->query = $query;

        return $this;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setFilter(Query $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function getArray()
    {
        if (null === $this->query && null === $this->filter) {
            throw new \RuntimeException('You must set query or filter to the filtered query');
        }

        $result = array();

        if (null !== $this->query) {
            $result['query'] = $this->query->getArray();
        }

        if (null !== $this->filter) {
            $result['filter'] = $this->filter->getArray();
        }

        return array(
            $this::QUERY_KEY => $result
        );
    }
}
