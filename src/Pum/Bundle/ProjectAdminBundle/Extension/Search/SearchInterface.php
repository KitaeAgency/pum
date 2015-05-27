<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Search;

interface SearchInterface
{
    public function search($q, $type, $limit, $page, $responseType);
}
