<?php

namespace Pum\Core\Extension\Search\Query;

class MultiMatch extends Query
{
    const QUERY_KEY = 'multi_match';

    private $fields = array();
    private $match;
    private $operator;
    private $type;
    private $fuzziness;

    public function __construct($match = null)
    {
        $this->match = $match;
    }

    public function setMatch($match)
    {
        $this->match = $match;

        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function setOperator($operator)
    {
        if (in_array(strtolower($operator), array('and', 'or'))) {
            $this->operator = $operator;
        }

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

        $result = array(
            'query'  => $this->match,
            'fields' => $this->fields
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
