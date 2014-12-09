<?php

namespace Pum\Core\Extension\Search\Query;

class Query
{
    public static function createQuery($type, $value = null)
    {
        switch ($type) {
            case 'filtered':
                return new Filtered();

            case 'query_string':
                return new QueryString($value);

            case 'bool':
                return new Bool();

            case 'match':
                return new Match($value);

            case 'wildcard':
                return new Wildcard($value);

            case 'fuzzy':
                return new Fuzzy($value);

            case 'fuzzy_like_this':
                return new FuzzyLikeThis($value);

            case 'regexp':
                return new Regexp($value);

            case 'multi_match':
                return new MultiMatch($value);

            case 'term':
                return new Term($value);

            case 'terms':
                return new Terms();

            case 'range':
                return new Range();

            default:
                throw new \RuntimeException('Unknow query type or unsupported type for now');
        }
    }
}
