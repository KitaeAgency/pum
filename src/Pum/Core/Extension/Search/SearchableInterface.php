<?php

namespace Pum\Core\Extension\Search;

interface SearchableInterface
{
    public function getSearchValues();
    public function getSearchWeights();
    public function getSearchIndexName();
    public function getSearchTypeName();
}
