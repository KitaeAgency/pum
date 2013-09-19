<?php

namespace Pum\Core\Context;

abstract class AbstractBuildContext
{
    protected $project;

    public function getBeams();
    public function getObject($name);
}
