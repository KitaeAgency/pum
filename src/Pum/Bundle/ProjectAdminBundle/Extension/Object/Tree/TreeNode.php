<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Object\Tree;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Core\Exception\TreeNodeNotFoundException;

class TreeNode
{
    protected $isRoot;
    protected $id;
    protected $label;
    protected $children;
    protected $attributes;

    /**
     * @param pum object
     */
    public function __construct($id, $label = '', $isRoot = false)
    {
        $this->id         = $id;
        $this->label      = $label;
        $this->isRoot     = $isRoot;
        $this->children   = new ArrayCollection();
        $this->attributes = array();
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
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return bool
     */
    public function isRoot()
    {
        return $this->isRoot;
    }

    /**
     * @param string $id id of TreeNode
     * @return bool
     */
    public function hasChild($id)
    {
        foreach ($this->children as $child) {
            if ($child->getId() === $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Pum\Core\Exception\TreeNodeNotFoundException
     */
    public function getChild($id)
    {
        foreach ($this->children as $child) {
            if ($child->getId() === $id) {
                return $child;
            }
        }

        throw new TreeNodeNotFoundException($id);
    }

    /**
     * @param TreeNode $treeNode
     * @return $this
     */
    public function addChild(TreeNode $treeNode)
    {
        $this->children->add($definition);

        return $this;
    }

    /**
     * @param TreeNode $treeNode
     * @return $this
     */
    public function removeChild(TreeNode $treeNode)
    {
        if ($this->children->contains($treeNode)) {
            $this->children->removeElement($treeNode);
        }

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return Mix
     */
    public function getAttribut($key, $default=null)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        return $default;
    }

    /**
     * @return $this
     */
    public function setAttribut($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function toArray()
    {
        return array();
    }
}
