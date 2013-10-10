<?php

namespace Pum\Core\Context;

use Pum\Core\ClassBuilder\ClassBuilder;
use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Definition\Project;

class FieldBuildContext extends AbstractBuildContext
{
    /**
     * @var FieldDefinition
     */
    protected $fieldDefinition;

    /**
     * @var array resolved options
     */
    protected $options;

    public function __construct(Project $project, ClassBuilder $classBuilder, FieldDefinition $fieldDefinition, array $options)
    {
        $this->fieldDefinition = $fieldDefinition;
        $this->options         = $options;

        parent::__construct($project, $classBuilder);
    }

    /**
     * @var FieldDefinition
     */
    public function getField()
    {
        return $this->fieldDefinition;
    }

    /**
     * @return ObjectDefinition
     */
    public function getObject()
    {
        return $this->fieldDefinition->getObject();
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
