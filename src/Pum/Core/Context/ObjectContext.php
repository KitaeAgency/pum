<?php

namespace Pum\Core\Context;

use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;

class ObjectContext extends AbstractContext
{
    /**
     * @var ObjectDefinition
     */
    protected $objectDefinition;

    /**
     * @var array resolved options
     */
    protected $options;

    public function __construct(Project $project, ObjectDefinition $objectDefinition, array $options)
    {
        $this->objectDefinition = $objectDefinition;
        $this->options         = $options;

        parent::__construct($project);
    }

    /**
     * @var ObjectDefinition
     */
    public function getObject()
    {
        return $this->objectDefinition;
    }

    /**
     * @throws InvalidArgumentException unknown argument
     */
    public function getOption($name)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new \InvalidArgumentException(sprintf('Option "%s" not found. Available are "%s".', $name, implode('", "', array_keys($this->options))));
        }

        return $this->options[$name];
    }
}
