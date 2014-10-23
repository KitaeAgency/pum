<?php

namespace Pum\Core\Extension\Search\Query;

class Range extends Query
{
    const QUERY_KEY = 'range';

    private $field;
    private $ranges = array();
    private $boost;

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

    public function addRange($operator, $value)
    {
        $ranges = array('gte', 'gt', 'lte', 'lt');

        if (!in_array($operator, $ranges)) {
            throw new \RuntimeException('Invalid range operator. Authorized operators are : %s', implode(',', $ranges));
        }

        $this->ranges[$operator] = $value;

        return $this;
    }

    public function getArray()
    {
        if (null === $this->field) {
            throw new \RuntimeException('You must set field to the query, null given');
        }

        $result = $this->ranges;

        if (null !== $this->boost) {
            $result['boost'] = $this->boost;
        }

        return array(
            $this::QUERY_KEY => array(
                $this->field => $result
            )
        );
    }
}
