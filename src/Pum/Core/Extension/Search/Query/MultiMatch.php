<?php

namespace Pum\Core\Extension\Search\Query;

class MultiMatch extends Query
{
    const QUERY_KEY = 'multi_match';

    private $fields = array();
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

    public function addField($field, $boost = 1)
    {
        if ($boost > 1) {
            $field = $field.'^'.$boost;
        }

        $this->fields[] = $field;

        return $this;
    }

    public function addFields($fields)
    {
        $this->fields = array_merge((array)$fields, $this->fields);

        return $this;
    }

    public function getArray()
    {
        if (empty($this->fields)) {
            throw new \RuntimeException('You must set at least one field to the query, null given');
        }

        return array(
            $this::QUERY_KEY => array(
                'query'  => $this->match,
                'fields' => $this->fields
            )
        );
    }
}
