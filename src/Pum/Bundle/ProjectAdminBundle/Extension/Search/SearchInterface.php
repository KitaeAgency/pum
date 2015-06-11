<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Search;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;

interface SearchInterface
{
    public function search($q, Beam $beam = null, ObjectDefinition $objectDefinition = null);
    public function clearCache();
}
