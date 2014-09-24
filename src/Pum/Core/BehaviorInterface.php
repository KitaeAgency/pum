<?php

namespace Pum\Core;

use Pum\Core\Context\ObjectContext;
use Pum\Core\Context\ObjectBuildContext;
use Doctrine\ORM\Mapping\ClassMetadata;

interface BehaviorInterface
{
    public function mapDoctrineObject(ObjectContext $context, ClassMetadata $metadata);

    public function buildObject(ObjectBuildContext $context);

    /**
     * @return string
     */
    public function getName();
}
