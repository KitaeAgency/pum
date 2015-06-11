<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Search;

use Symfony\Component\HttpFoundation\Request;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;

interface SearchInterface
{
    public function search(Request $request, Beam $beam = null, ObjectDefinition $objectDefinition = null);
    public function clearCache();
}
