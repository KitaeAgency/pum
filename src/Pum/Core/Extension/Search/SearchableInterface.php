<?php

namespace Pum\Core\Extension\Routing;

interface SearchableInterface
{
    public function getSearchValues();
    public function getSearchIndexName();
}
