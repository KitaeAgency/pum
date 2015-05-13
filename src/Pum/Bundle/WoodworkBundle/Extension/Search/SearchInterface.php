<?php

namespace Pum\Bundle\WoodworkBundle\Extension\Search;

use Pum\Core\Context\FieldBuildContext;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

interface SearchInterface
{
    public function search($q, $type, $responseType, $limit, $page);
}
