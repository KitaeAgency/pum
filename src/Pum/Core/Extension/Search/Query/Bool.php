<?php

namespace Pum\Core\Extension\Search\Query;

use Elasticsearch\Client;

class Bool extends Query
{
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
        return;
    }
}
