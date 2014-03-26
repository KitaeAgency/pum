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
            $array['must'] = $this->musts;
        }

        if (!empty($this->mustNots)) {
            $array['must_not'] = $this->mustNots;
        }

        if (!empty($this->musts)) {
            $array['should'] = $this->shoulds;
        }

        return array(
            $this::QUERY_KEY => $array
        );
    }
}
