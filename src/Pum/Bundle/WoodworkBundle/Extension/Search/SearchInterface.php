<?php

namespace Pum\Bundle\WoodworkBundle\Extension\Search;

use Pum\Core\Context\FieldBuildContext;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

interface SearchInterface
{
    const SEARCH_TYPE_PROJECT = 'project';
    const SEARCH_TYPE_BEAM    = 'beam';
    const SEARCH_TYPE_OBJECT  = 'object';
    const SEARCH_TYPE_GROUP   = 'group';
    const SEARCH_TYPE_USER    = 'user';
    const SEARCH_TYPE_ALL     = 'all';

    public function search($q, $type, $responseType);
}
