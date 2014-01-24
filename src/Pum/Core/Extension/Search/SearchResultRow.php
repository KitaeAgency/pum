<?php

namespace Pum\Core\Extension\Search;

class SearchResultRow
{
    private $row;

    public function __construct(array $row)
    {
        $this->row = $row;
    }

    public function getId()
    {
        return $this->row['_id'];
    }

    public function getType()
    {
        return $this->row['_type'];
    }

    public function get($name)
    {
        return isset($this->row['_source'][$name]) ? $this->row['_source'][$name] : null;
    }
}
