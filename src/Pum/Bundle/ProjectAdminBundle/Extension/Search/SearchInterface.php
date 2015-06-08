<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Search;

interface SearchInterface
{
    public function count($q, $beamName, $objectName);
    public function search($q, $objectName);
}
