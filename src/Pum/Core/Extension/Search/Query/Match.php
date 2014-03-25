<?php

namespace Pum\Core\Extension\Search\Query;

use Elasticsearch\Client;

class Match extends Query
{
    const QUERY_KEY = 'match';

    private $field;
    private $match;

    public function __construct($match = null)
    {
        $this->match = $match;
    }

    public function setMatch($match)
    {
        $this->match = $match;

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
                $this->field => $this->match
            )
        );
    }
}
