<?php

namespace Pum\Core\Extension\Search\Result;

class Count
{
    private $result;

    public function __construct(array $result)
    {
        $this->result  = $result;
    }

    public function getCount($default = 0)
    {
        return isset($this->result['count']) ? $this->result['count'] : $default;
    }

    public function getShards($default = null)
    {
        return isset($this->result['_shards']) ? $this->result['_shards'] : $default;
    }

    public function __toString()
    {
        return (string) $this->getCount();
    }
}
