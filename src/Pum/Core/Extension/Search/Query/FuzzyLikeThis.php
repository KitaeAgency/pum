<?php

namespace Pum\Core\Extension\Search\Query;

class FuzzyLikeThis extends Query
{
    const QUERY_KEY = 'fuzzy_like_this';

    private $fields = array();
    private $match;
    private $max_query_terms;
    private $fuzziness;
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

    public function setBoost($boost)
    {
        $this->boost = $boost;

        return $this;
    }

    public function setMaxQueryTerms($max_query_terms)
    {
        $this->max_query_terms = $max_query_terms;

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

    public function addField($field)
    {
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
            'like_text' => $this->match,
            'fields'    => $this->fields
        );

        if (null !== $this->max_query_terms) {
            $result['max_query_terms'] = $this->max_query_terms;
        }

        if (null !== $this->fuzziness) {
            $result['fuzziness'] = $this->fuzziness;
        }

        if (null !== $this->boost) {
            $result['boost'] = $this->boost;
        }

        return array(
            $this::QUERY_KEY => $result
        );
    }
}
