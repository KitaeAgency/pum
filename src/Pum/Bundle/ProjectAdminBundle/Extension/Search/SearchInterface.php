<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Search;

interface SearchInterface
{
    public function count($q, $objectName);
    public function search($q, $objectName);
}
