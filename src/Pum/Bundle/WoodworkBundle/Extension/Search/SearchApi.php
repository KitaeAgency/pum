<?php

namespace Pum\Bundle\WoodworkBundle\Extension\Search;

/**
 * Class SearchApi
 * @package Pum\Bundle\WoodworkBundle\Extension\Search
 */
class SearchApi
{
    /**
     * @var EntityManager
     */
    protected $search;

    public function __construct(SearchInterface $search)
    {
        $this->search = $search;
    }

    public function search($q, $type = Search::SEARCH_TYPE_ALL, $responseType = Search::RESPONSE_FORMAT, $limit = Search::DEFAULT_LIMIT, $page = 1)
    {
        return $this->search->search($q, $type, $responseType, $limit, $page);
    }
}
