<?php

namespace Pum\Core\Definition;

use Pum\Core\Event\ObjectDefinitionEvent;
use Pum\Core\Events;

class SearchField extends EventObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var ObjectDefinition
     */
    protected $objectDefinition;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $expression;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $weight;

    /**
     * @var string
     */
    protected $index;

    /**
     * Constructor
     *
     * @param ObjectDefinition $objectDefinition
     */
    public function __construct(ObjectDefinition $objectDefinition = null)
    {
        $this->objectDefinition = $objectDefinition;
        $this->weight = 1;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return SearchField
     */
    public function setName($name)
    {
        if ($name != $this->name && $this->objectDefinition) {
            $this->objectDefinition->raiseOnce(Events::OBJECT_DEFINITION_SEARCH_UPDATE, new ObjectDefinitionEvent($this->objectDefinition));
        }

        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param string $expression
     * @return SearchField
     */
    public function setExpression($expression)
    {
        if ($expression != $this->expression && $this->objectDefinition) {
            $this->raiseOnce(Events::OBJECT_DEFINITION_SEARCH_UPDATE, new ObjectDefinitionEvent($this->objectDefinition));
        }

        $this->expression = $expression;

        return $this;
    }

    /**
     * @return ObjectDefinition
     */
    public function getObjectDefinition()
    {
        return $this->objectDefinition;
    }

    public function setObjectDefinition(ObjectDefinition $objectDefinition = null)
    {
        $this->objectDefinition = $objectDefinition;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     * @return SearchField
     */
    public function setWeight($weight)
    {
        if ($weight != $this->weight && $this->objectDefinition) {
            $this->objectDefinition->raiseOnce(Events::OBJECT_DEFINITION_SEARCH_UPDATE, new ObjectDefinitionEvent($this->objectDefinition));
        }

        $this->weight = $weight;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return SearchField
     */
    public function setType($type)
    {
        if ($type != $this->type && $this->objectDefinition) {
            $this->objectDefinition->raiseOnce(Events::OBJECT_DEFINITION_SEARCH_UPDATE, new ObjectDefinitionEvent($this->objectDefinition));
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param string $index
     * @return SearchField
     */
    public function setIndex($index)
    {
        if ($index != $this->index && $this->objectDefinition) {
            $this->objectDefinition->raiseOnce(Events::OBJECT_DEFINITION_SEARCH_UPDATE, new ObjectDefinitionEvent($this->objectDefinition));
        }

        $this->index = $index;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'name'       => $this->name,
            'expression' => $this->expression,
            'type'       => $this->type,
            'weight'     => $this->weight,
            'index'      => $this->index
        );
    }

    /**
     * @param array $array
     * @return SearchField
     */
    public static function createFromArray(array $array)
    {
        $instance = new self();

        if (isset($array['name'])) {
            $instance->setName($array['name']);
        }

        if (isset($array['expression'])) {
            $instance->setExpression($array['expression']);
        }

        if (isset($array['type'])) {
            $instance->setType($array['type']);
        }

        if (isset($array['weight'])) {
            $instance->setWeight($array['weight']);
        }

        if (isset($array['index'])) {
            $instance->setIndex($array['index']);
        }

        return $instance;
    }
}
