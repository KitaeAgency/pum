<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Search;

interface SearchInterface
{
    public function count($q, $objectName, $responseType);
    public function search($q, $objectName, $page, $limit, $responseType);
}
