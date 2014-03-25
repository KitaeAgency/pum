<?php

namespace Pum\Core\Extension\Search\Query;

use Elasticsearch\Client;

class Regexp extends Match
{
    const QUERY_KEY = 'regexp';
}
