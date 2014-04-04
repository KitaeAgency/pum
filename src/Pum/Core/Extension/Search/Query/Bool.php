<?php

namespace Pum\Core\Extension\Search\Query;

class Bool extends Query
{
    const QUERY_KEY = 'bool';

    private $musts    = array();
    private $mustNots = array();
    private $shoulds  = array();

    private $boost;
    private $minimumShouldMatch;

    public function addMust(Query $query)
    {
        $this->musts[] = $query;

        return $this;
    }

    public function addMustNot(Query $query)
    {
        $this->mustNots[] = $query;

        return $this;
    }

    public function addShould(Query $query)
    {
        $this->shoulds[] = $query;

        return $this;
    }

    public function setMinimumShouldMatch($minimumShouldMatch)
    {
        $this->minimumShouldMatch = $minimumShouldMatch;

        return $this;
    }

    public function setBoost($boost)
    {
        $this->boost = $boost;

        return $this;
    }

    public function getArray()
    {
        $queries = array();

        if (!empty($this->musts)) {
            foreach ($this->musts as $query) {
                $queries['must'][] = $query->getArray();
            }
        }

        if (!empty($this->mustNots)) {
            foreach ($this->mustNots as $query) {
                $queries['must_not'][] = $query->getArray();
            }
        }

        if (!empty($this->shoulds)) {
            foreach ($this->shoulds as $query) {
                $queries['should'][] = $query->getArray();
            }
        }

        $array = array(
            $this::QUERY_KEY => $queries
        );

        if (null !== $this->boost) {
            $array['boost'] = $this->boost;
        }

        if (null !== $this->minimumShouldMatch) {
            $array['minimum_should_match'] = $this->minimumShouldMatch;
        }

        return $array;
    }
}
