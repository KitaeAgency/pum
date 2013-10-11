<?php

namespace Pum\Core;

interface BehaviorInterface
{
    public function buildObject(ObjectBuildContext $context);

    /**
     * @return string
     */
    public function getName()
    {
    }
}
