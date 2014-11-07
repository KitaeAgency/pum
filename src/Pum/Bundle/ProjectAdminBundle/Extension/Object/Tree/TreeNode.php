<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Object\Tree;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Core\Exception\TreeNodeNotFoundException;

class TreeNode
{
    protected $isRoot;
    protected $id;
    protected $label;
    protected $icon;
    protected $states;
    protected $children;
    protected $hasChildren;
    protected $childrenDetail;
    protected $li_attr;
    protected $a_attr;

    /**
     * @param
     */
    public function __construct($id, $label = '', $icon = null, $type = null, $isRoot = false)
    {
        $this->id             = $id;
        $this->label          = $label;
        $this->icon           = $icon;
        $this->type           = $type;
        $this->isRoot         = $isRoot;
        $this->children       = new ArrayCollection();
        $this->states         = array();
        $this->hasChildren    = false;
        $this->childrenDetail = false;
        $this->li_attr        = array();
        $this->a_attr         = array();
    }

    /**
     * @param
     */
    public static function create($id, $label = '', $icon = null, $type = null, $isRoot = false)
    {
        return new self($id, $label, $icon, $type, $isRoot);
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
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

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
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getChildrenDetail()
    {
        return $this->childrenDetail;
    }

    /**
     * @return $this
     */
    public function setHasChildren($hasChildren)
    {
        $this->hasChildren = $hasChildren;

        return $this;
    }

    /**
     * @return string
     */
    public function getHasChildren()
    {
        return $this->hasChildren;
    }

    /**
     * @return $this
     */
    public function setChildrenDetail($childrenDetail)
    {
        $this->childrenDetail = $childrenDetail;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRoot()
    {
        return $this->isRoot;
    }

    /**
     * @return $this
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
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
        $this->children->add($treeNode);

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
     * @return Mix
     */
    public function getStates($key, $default=null)
    {
        if (isset($this->states[$key])) {
            return $this->states[$key];
        }

        return $default;
    }

    /**
     * @return $this
     */
    public function setState($key, $value)
    {
        $this->states[$key] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function setLiAttr($key, $value)
    {
        $this->li_attr[$key] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function setLiAttrs($values)
    {
        $this->li_attr = array_merge($this->li_attr, $values);

        return $this;
    }

    /**
     * @return Mix
     */
    public function getLiAttr($key, $default=null)
    {
        if (isset($this->li_attr[$key])) {
            return $this->li_attr[$key];
        }

        return $default;
    }

    /**
     * @return $this
     */
    public function setAAttr($key, $value)
    {
        $this->a_attr[$key] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function setAAttrs($values)
    {
        $this->a_attr = array_merge($this->a_attr, $values);

        return $this;
    }

    /**
     * @return Mix
     */
    public function getAAttr($key, $default=null)
    {
        if (isset($this->a_attr[$key])) {
            return $this->a_attr[$key];
        }

        return $default;
    }


    public function toArray()
    {
        $result = array(
            'id'   => $this->id,
            'text' => $this->label,
        );

        if ($this->isRoot) {
            $result['parent'] = '#';
        }

        if ($this->type) {
            $result['type'] = $this->type;
        }

        if ($this->icon) {
            $result['icon'] = $this->icon;
        }

        if (!empty($this->states)) {
            $result['state'] = $this->states;
        }

        if ($children = $this->hasChildren) {
            if (true === $this->childrenDetail) {
                    $children = array();

                foreach ($this->children as $child) {
                    $children[] = $child->toArray();
                }
            }
        }

        $result['children'] = $children;

        if (!empty($this->li_attr)) {
            $result['li_attr'] = $this->li_attr;
        }

        if (!empty($this->a_attr)) {
            $result['a_attr'] = $this->a_attr;
        }

        return $result;
    }
}
