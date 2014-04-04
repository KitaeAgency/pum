<?php

namespace Pum\Core\Extension\Search\Query;

class Fuzzy extends Query
{
    const QUERY_KEY = 'fuzzy';

    private $field;
    private $match;
    private $boost;
    private $fuzziness;
    private $prefix_length;
    private $max_expansions;

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

    public function setBoost($boost)
    {
        $this->boost = $boost;

        return $this;
    }

    public function setPrefixLength($prefix_length)
    {
        $this->prefix_length = $prefix_length;

        return $this;
    }

    public function setMaxExpansions($max_expansions)
    {
        $this->max_expansions = $max_expansions;

        return $this;
    }

    public function getArray()
    {
        if (null === $this->field) {
            throw new \RuntimeException('You must set field to the query, null given');
        }

        $result['value'] = $this->match;

        if (null !== $this->boost) {
            $result['boost'] = $this->boost;
        }

        if (null !== $this->fuzziness) {
            $result['fuzziness'] = $this->fuzziness;
        }

        if (null !== $this->prefix_length) {
            $result['prefix_length'] = $this->prefix_length;
        }

        if (null !== $this->max_expansions) {
            $result['max_expansions'] = $this->max_expansions;
        }

        return array(
            $this::QUERY_KEY => array(
                $this->field => $result
            )
        );
    }
}
