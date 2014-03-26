<?php

namespace Pum\Core\Extension\Search\Query;

use Elasticsearch\Client;

class Bool extends Query
{
    const QUERY_KEY = 'bool';

    private $musts    = array();
    private $mustNots = array();
    private $shoulds  = array();

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

    public function getArray()
    {
        $array = array();

        if (!empty($this->musts)) {
            foreach ($this->musts as $query) {
                $array['must'][] = $query->getArray();
            }
        }

        if (!empty($this->mustNots)) {
            foreach ($this->mustNots as $query) {
                $array['must_not'][] = $query->getArray();
            }
        }

        if (!empty($this->shoulds)) {
            foreach ($this->shoulds as $query) {
                $array['should'][] = $query->getArray();
            }
        }

        return array(
            $this::QUERY_KEY => $array
        );
    }
}
