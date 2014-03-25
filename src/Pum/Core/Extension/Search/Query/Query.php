<?php

namespace Pum\Core\Extension\Search\Query;

use Elasticsearch\Client;

class Query
{
    public static function createQuery($type)
    {
        switch ($type) {
            case 'bool':
                return new Bool();

            default:
                throw new \RuntimeException('Unknow query type or unsupported type for now');
        }
    }
}
