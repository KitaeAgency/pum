<?php

namespace Pum\Core\Definition;

use Pum\Core\Events;

class SearchField
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
     * @return SearchField
     */
    public function setName($name)
    {
        if ($name != $this->name && $this->objectDefinition) {
            $this->objectDefinition->storeEvent(Events::INDEX_CHANGE);
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
     * @return SearchField
     */
    public function setExpression($expression)
    {
        if ($expression != $this->expression && $this->objectDefinition) {
            $this->objectDefinition->storeEvent(Events::INDEX_CHANGE);
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
     * @return SearchField
     */
    public function setWeight($weight)
    {
        if ($weight != $this->weight && $this->objectDefinition) {
            $this->objectDefinition->storeEvent(Events::INDEX_CHANGE);
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
     * @return SearchField
     */
    public function setType($type)
    {
        if ($type != $this->type && $this->objectDefinition) {
            $this->objectDefinition->storeEvent(Events::INDEX_CHANGE);
        }

        $this->type = $type;

        return $this;
    }

    public function toArray()
    {
        return array(
            'name'       => $this->name,
            'expression' => $this->expression,
            'type'       => $this->type,
            'weight'     => $this->weight
        );
    }

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

        return $instance;
    }
}
