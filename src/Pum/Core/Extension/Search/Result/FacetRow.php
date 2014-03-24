<?php

namespace Pum\Core\Extension\Search\Result;

class FacetRow
{
    private $row;

    public function __construct(array $row)
    {
        $this->row = $row;
    }

    /* 
     *
     * Generic Properties 
     *
     */
    public function get($name, $default = null)
    {
        return isset($this->row[$name]) ? $this->row[$name] : null;
    }

    public function getCount($default = 0)
    {
        return isset($this->row['count']) ? $this->row['count'] : 0;
    }

    /* 
     *
     * Terms Properties 
     *
     */
    public function getTerm($default = null)
    {
        return isset($this->row['term']) ? $this->row['term'] : $default;
    }

    /* 
     *
     * Ranges Properties 
     *
     */
    public function getFrom($default = null)
    {
        return isset($this->row['from']) ? $this->row['from'] : $default;
    }

    public function getTo($default = null)
    {
        return isset($this->row['to']) ? $this->row['to'] : $default;
    }

    public function getMin($default = null)
    {
        return isset($this->row['min']) ? $this->row['min'] : $default;
    }

    public function getMax($default = null)
    {
        return isset($this->row['max']) ? $this->row['max'] : $default;
    }

    public function getTotal($default = null)
    {
        return isset($this->row['total']) ? $this->row['total'] : $default;
    }

    public function getTotalCount($default = null)
    {
        return isset($this->row['total_count']) ? $this->row['total_count'] : $default;
    }

    public function getMean($default = null)
    {
        return isset($this->row['mean']) ? $this->row['mean'] : $default;
    }
}
