<?php

namespace Pum\Core;

interface BehaviorInterface
{
    public function buildObject(ObjectBuildContext $context);
}
