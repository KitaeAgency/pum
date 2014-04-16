<?php

namespace Pum\Core\Extension\Search\Query;

class QueryString extends Query
{
    const QUERY_KEY = 'query_string';

    private $query;
    private $default_field;
    private $fields = array();
    private $default_operator;
    private $fuzziness;
    private $boost;
    private $use_dis_max;

    public function __construct($query = null)
    {
        $this->query = $query;
    }

    public function setQuery($query)
    {
        $this->query = $query;

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

    public function setDefaultField($default_field)
    {
        $this->default_field = $default_field;

        return $this;
    }

    public function setDefaultOperator($default_operator)
    {
        $this->default_operator = $default_operator;

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

    public function setBoost($boost)
    {
        $this->boost = $boost;

        return $this;
    }

    public function setUseDisMax($use_dis_max)
    {
        $this->use_dis_max = (bool) $use_dis_max;

        return $this;
    }

    public function getArray()
    {
        if (null === $this->query) {
            throw new \RuntimeException('You must set query to the query string query');
        }

        $result = array();

        $result['query'] = $this->query;

        if (null !== $this->default_field) {
            $result['default_field'] = $this->default_field;
        }

        if (!empty($this->fields)) {
            $result['fields'] = $this->fields;
        }

        if (null !== $this->default_operator) {
            $result['default_operator'] = $this->default_operator;
        }

        if (null !== $this->fuzziness) {
            $result['fuzziness'] = $this->fuzziness;
        }

        if (null !== $this->boost) {
            $result['boost'] = $this->boost;
        }

        if (null !== $this->use_dis_max) {
            $result['use_dis_max'] = $this->use_dis_max;
        }

        return array(
            $this::QUERY_KEY => $result
        );
    }
}
