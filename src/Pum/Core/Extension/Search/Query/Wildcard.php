<?php

namespace Pum\Core\Extension\Search\Query;

class Wildcard extends Query
{
    const QUERY_KEY = 'wildcard';

    private $field;
    private $match;
    private $boost;


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

    public function setBoost($boost)
    {
        $this->boost = $boost;

        return $this;
    }

    public function getArray()
    {
        if (null === $this->field) {
            throw new \RuntimeException('You must set field to the query, null given');
        }

        $options['value'] = $this->match;

        if (null !== $this->boost) {
            $options['boost'] = $this->boost;
        }

        $result = array(
            $this->field => $options
        );

        return array(
            $this::QUERY_KEY => $result
        );
    }
}
