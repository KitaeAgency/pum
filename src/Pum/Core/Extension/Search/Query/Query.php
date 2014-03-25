<?php

namespace Pum\Core\Extension\Search\Query;

use Elasticsearch\Client;

class Query
{
    public static function createQuery($type, $value = null)
    {
        switch ($type) {
            case 'bool':
                return new Bool();

            case 'match':
                return new Match($value);

            case 'regexp':
                return new Regexp($value);

            case 'multi_match':
                return new MultiMatch($value);

            case 'term':
                return new Term($value);

            case 'terms':
                return new Terms();

            default:
                throw new \RuntimeException('Unknow query type or unsupported type for now');
        }
    }
}
