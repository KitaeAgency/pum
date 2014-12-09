<?php

namespace Pum\Core\Extension\Search\Query;

class Match extends Query
{
    const QUERY_KEY = 'match';

    private $field;
    private $match;
    private $operator;
    private $fuzziness;
    private $type;
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

    public function setFuzziness($fuzziness)
    {
        $this->fuzziness = $fuzziness;

        return $this;
    }

    public function autoFuzziness()
    {
        $this->fuzziness = 'AUTO';

        return $this;
    }

    public function setOperator($operator)
    {
        if (in_array(strtolower($operator), array('and', 'or'))) {
            $this->operator = $operator;
        }

        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;

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

        $options['query'] = $this->match;

        if (null !== $this->operator) {
            $options['operator'] = $this->operator;
        }

        if (null !== $this->fuzziness) {
            $options['fuzziness'] = $this->fuzziness;
        }

        if (null !== $this->type) {
            $options['type'] = $this->type;
        }

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
