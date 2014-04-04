<?php

namespace Pum\Core\Extension\Search\Query;

class Term extends Query
{
    const QUERY_KEY = 'term';

    private $field;
    private $term;
    private $boost;

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

        if (null === $this->boost) {
            $term = $this->term;
        } else {
            $term = array(
                'value' => $this->term,
                'boost' => $this->boost
            );
        }

        return array(
            $this::QUERY_KEY => array(
                $this->field => $term
            )
        );
    }
}
