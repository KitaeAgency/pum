<?php

namespace Pum\Core\Extension\Search\Result;

class Row
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

    public function getIndex()
    {
        return $this->row['_index'];
    }

    public function getType()
    {
        return $this->row['_type'];
    }

    public function getScore()
    {
        return $this->row['_score'];
    }

    public function get($name, $default = null)
    {
        switch (true) {
            case isset($this->row['fields'][$name]):
                if (count($this->row['fields'][$name]) === 1) {
                    return $this->row['fields'][$name][0];
                }

                return $this->row['fields'][$name];

            case isset($this->row['_source'][$name]):
                return $this->row['_source'][$name];

            default:
                return $default;
        }
    }
}
