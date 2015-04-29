<?php

namespace Pum\Core\Context;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Definition\Project;

class FieldContext extends AbstractContext
{
    /**
     * @var FieldDefinition
     */
    protected $fieldDefinition;

    /**
     * @var array resolved options
     */
    protected $options;

    public function __construct(Project $project, FieldDefinition $fieldDefinition, array $options)
    {
        $this->fieldDefinition = $fieldDefinition;
        $this->options         = $options;

        parent::__construct($project);
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
    public function getOption($name, $default = null)
    {
        if (!array_key_exists($name, $this->options)) {
            return $default;
        }

        return $this->options[$name];
    }
}
