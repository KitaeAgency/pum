<?php

namespace Pum\Core;

use Pum\Core\Context\ObjectBuildContext;

interface BehaviorInterface
{
    public function buildObject(ObjectBuildContext $context);

    /**
     * @return string
     */
    public function getName();
}
