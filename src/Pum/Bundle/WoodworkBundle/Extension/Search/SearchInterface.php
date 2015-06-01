<?php

namespace Pum\Bundle\WoodworkBundle\Extension\Search;

interface SearchInterface
{
    public function search($q, $type, $limit, $page);
}
