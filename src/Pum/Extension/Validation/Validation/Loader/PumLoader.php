<?php

namespace Pum\Extension\Validation\Validation\Loader;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Loader\LoaderInterface;

class PumLoader implements LoaderInterface
{
    public function loadClassMetadata(ClassMetadata $metadata)
    {
        $className = $metadata->getClassname();

        die('@todo load validation');
    }
}
