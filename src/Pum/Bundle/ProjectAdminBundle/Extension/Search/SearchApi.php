<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Search;

/**
 * Class SearchApi
 * @package Pum\Bundle\ProjectAdminBundle\Extension\Search
 */
class SearchApi
{
    /**
     * @var SearchInterface
     */
    protected $search;

    public function __construct(SearchInterface $search)
    {
        $this->search = $search;
    }

    public function count($q, $objectName = Search::SEARCH_ALL, $responseType = Search::RESPONSE_FORMAT)
    {
        return $this->search->count($q, $objectName, $responseType);
    }

    public function search($q, $objectName = Search::SEARCH_ALL, $page = 1, $limit = Search::DEFAULT_LIMIT, $responseType = Search::RESPONSE_FORMAT)
    {
        return $this->search->search($q, $objectName, $page, $limit, $responseType);
    }
}
