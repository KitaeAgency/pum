<?php

namespace Pum\Bundle\WoodworkBundle\Extension\Search;

/**
 * Class SearchApi
 * @package Pum\Bundle\WoodworkBundle\Extension\Search
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

    public function search($q, $type = Search::SEARCH_TYPE_ALL)
    {
        return $this->search->search($q, $type);
    }
}
