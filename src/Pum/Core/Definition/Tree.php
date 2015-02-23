<?php

namespace Pum\Core\Definition;

class Tree extends EventObject
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $icon;

    /**
     * @var ObjectDefinition
     */
    protected $object;

    /**
     * @var FieldDefinition
     */
    protected $labelField;

    /**
     * @var FieldDefinition
     */
    protected $treeField;

    /**
     * @param ObjectDefinition $objectDefinition
     * @param string $name name of the table view.
     */
    public function __construct(ObjectDefinition $object = null)
    {
        $this->object = $object;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return ObjectDefinition
     */
    public function getObjectDefinition()
    {
        return $this->objectDefinition;
    }

    /**
     * @return Tree
     */
    public function setObjectDefinition($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return FieldDefinition
     */
    public function getLabelField()
    {
        return $this->labelField;
    }

    /**
     * @return Tree
     */
    public function setLabelField($labelField)
    {
        $this->labelField = $labelField;

        return $this;
    }

    /**
     * @return FieldDefinition
     */
    public function getTreeField()
    {
        return $this->treeField;
    }

    /**
     * @return Tree
     */
    public function setTreeField($treeField)
    {
        $this->treeField = $treeField;

        return $this;
    }

    /**
     * Returns $this as an array
     */
    public function toArray()
    {
        return array(
            'icon'        => $this->icon,
            'label_field' => $this->labelField ? $this->labelField->getName() : null,
            'tree_field'  => $this->treeField ? $this->treeField->getName() : null
        );
    }

    /**
     * @param array $array
     * @return Tree
     */
    public static function createFromArray(array $array, ObjectDefinition $object)
    {
        $instance = new self($object);

        if (isset($array['icon'])) {
            $instance->setIcon($array['icon']);
        }

        if (isset($array['label_field']) && $array['label_field']) {
            if ($object->hasField($array['label_field'])) {
                $instance->setLabelField($object->getField($array['label_field']));
            }
        }

        if (isset($array['tree_field']) && $array['tree_field']) {
            if ($object->hasField($array['tree_field'])) {
                $instance->setTreeField($object->getField($array['tree_field']));
            }
        }

        return $instance;
    }
}
