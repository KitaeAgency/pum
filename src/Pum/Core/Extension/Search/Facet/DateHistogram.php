<?php

namespace Pum\Core\Extension\Search\Facet;

use Elasticsearch\Client;

class DateHistogram extends Histogram
{
    const FACET_KEY = 'date_histogram';
}
