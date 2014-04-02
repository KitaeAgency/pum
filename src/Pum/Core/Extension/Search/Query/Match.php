<?php

namespace Pum\Core\Extension\Search\Query;

use Elasticsearch\Client;

class Match extends Query
{
    const QUERY_KEY = 'match';

    private $field;
    private $match;
    private $operator;
    private $fuzziness;
    private $type;

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

    public function getArray()
    {
        if (null === $this->field) {
            throw new \RuntimeException('You must set field to the query, null given');
        }

        $result = array(
            $this->field => $this->match
        );

        if (null !== $this->operator) {
            $result['operator'] = $this->operator;
        }

        if (null !== $this->fuzziness) {
            $result['fuzziness'] = $this->fuzziness;
        }

        if (null !== $this->type) {
            $result['type'] = $this->type;
        }

        return array(
            $this::QUERY_KEY => $result
        );
    }
}
