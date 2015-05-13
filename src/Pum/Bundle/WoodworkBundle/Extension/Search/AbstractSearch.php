<?php

namespace Pum\Bundle\WoodworkBundle\Extension\Search;

use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractSearch implements SearchInterface
{
    public static $searchTypes = array(
        self::SEARCH_TYPE_PROJECT,
        self::SEARCH_TYPE_BEAM,
        self::SEARCH_TYPE_OBJECT,
        self::SEARCH_TYPE_GROUP,
        self::SEARCH_TYPE_USER,
        self::SEARCH_TYPE_ALL,
    );

    public function search($q, $type, $responseType)
    {
    }
}
