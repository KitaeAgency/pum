<?php

namespace Pum\Core\Definition;

class SearchField
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var FieldDefition
     */
    protected $fieldDefinition;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $weight;

    public function __construct(FieldDefinition $fieldDefinition = null)
    {
        $this->fieldDefinition = $fieldDefinition;
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
        $this->name = $name;

        return $this;
    }

    /**
     * @return FieldDefinition
     */
    public function getFieldDefinition()
    {
        return $this->fieldDefinition;
    }

    public function setFieldDefinition(FieldDefinition $fieldDefinition = null)
    {
        $this->fieldDefinition = $fieldDefinition;
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
        $this->weight = $weight;

        return $this;
    }
}
