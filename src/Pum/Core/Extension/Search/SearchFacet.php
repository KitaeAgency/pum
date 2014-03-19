<?php

namespace Pum\Core\Extension\Search;

use Elasticsearch\Client;

class SearchFacet
{
    private $type;
    private $field;

    public function __construct($type = 'tags')
    {
        $this->type = $type;
    }

}
