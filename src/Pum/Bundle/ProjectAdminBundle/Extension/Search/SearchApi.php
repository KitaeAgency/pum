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

    public function count($q, $objectName, $responseType = Search::RESPONSE_FORMAT)
    {
        return $this->search->count($q, $objectName, $responseType);
    }

    public function search($q, $objectName, $page = 1, $limit = Search::DEFAULT_LIMIT)
    {
        return $this->search->search($q, $objectName, $page, $limit);
    }
}
