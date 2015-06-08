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

    public function count($q, $beamName, $objectName)
    {
        return $this->search->count($q, $beamName, $objectName);
    }

    public function search($q, $objectName)
    {
        return $this->search->search($q, $objectName);
    }

    public function clearSchemaCache()
    {
        return $this->search->clearSchemaCache();
    }
}
