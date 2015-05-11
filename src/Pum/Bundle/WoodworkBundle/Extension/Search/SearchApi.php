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

    public function search($q = null, $type = SearchInterface::SEARCH_TYPE_ALL, $responseType = 'JSON')
    {
        return $this->search->search($q, $type, $responseType);
    }
}
