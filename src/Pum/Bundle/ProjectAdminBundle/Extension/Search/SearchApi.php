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

    public function search($request, $beam, $objectDefinition)
    {
        return $this->search->search($request, $beam, $objectDefinition);
    }

    public function clearSchemaCache()
    {
        return $this->search->clearSchemaCache();
    }
}
