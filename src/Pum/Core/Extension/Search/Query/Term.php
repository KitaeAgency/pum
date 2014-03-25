<?php

namespace Pum\Core\Extension\Search\Query;

use Elasticsearch\Client;

class Term extends Query
{
    const QUERY_KEY = 'term';

    private $field;
    private $term;

    public function __construct($term = null)
    {
        $this->term = $term;
    }

    public function setTerm($term)
    {
        $this->term = $term;

        return $this;
    }

    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    public function getArray()
    {
        if (null === $this->field) {
            throw new \RuntimeException('You must set field to the query, null given');
        }

        return array(
            $this::QUERY_KEY => array(
                $this->field => $this->term
            )
        );
    }
}
