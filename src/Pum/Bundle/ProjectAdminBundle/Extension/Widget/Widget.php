<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Widget;

use Doctrine\Common\Collections\ArrayCollection;

class Widget implements WidgetInterface
{
    const DEFAULT_ICON   = 'settings2';
    const DEFAULT_COLOR  = 'concrete';
    const DEFAULT_WEIGHT = 20;

    private $name;
    private $color;
    private $icon;
    private $weight;

    private $label;
    private $labelParameters;

    private $route;
    private $routeParameters;

    private $permission;
    private $permissionParameters;

    private $groups;

    public function __construct($name, $icon = null, $color = null, $weight = null, $uid = null)
    {
        $this->setName($name);
        $this->setLabel($name);
        $this->setIcon($icon);
        $this->setColor($color);
        $this->setWeight($weight);

        $this->labelParameters      = array();
        $this->routeParameters      = array();
        $this->permissionParameters = array();

        $this->groups               = new ArrayCollection();
    }

    public static function create($name, $icon = null, $color = null, $weight = null, $uid = null)
    {
        return new self($name, $icon, $color, $weight, $uid);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label, array $labelParameters = array())
    {
        $this->label = $label;
        foreach ($labelParameters as $key => $value) {
            $this->addLabelParameter($key, $value);
        }

        return $this;
    }

    public function getLabelParameters()
    {
        return $this->labelParameters;
    }

    public function addLabelParameter($key, $value)
    {
        $this->labelParameters[$key] = $value;

        return $this;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($color)
    {
        $this->color = (null !== $color) ? $color : self::DEFAULT_COLOR;

        return $this;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon($icon)
    {
        $this->icon = (null !== $icon) ? $icon : self::DEFAULT_ICON;

        return $this;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setRoute($route, array $routeParameters = array())
    {
        $this->route = $route;
        foreach ($routeParameters as $key => $value) {
            $this->addRouteParameter($key, $value);
        }

        return $this;
    }

    public function getRouteParameters()
    {
        return $this->routeParameters;
    }

    public function addRouteParameter($key, $value)
    {
        $this->routeParameters[$key] = $value;

        return $this;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function setWeight($weight)
    {
        $this->weight = (null !== $weight) ? $weight : self::DEFAULT_WEIGHT;

        return $this;
    }

    public function getPermission()
    {
        return $this->permission;
    }

    public function setPermission($permission, array $permissionParameters = array())
    {
        $this->permission = $permission;
        foreach ($permissionParameters as $key => $value) {
            $this->addPermissionParameter($key, $value);
        }

        return $this;
    }

    public function getPermissionParameters()
    {
        return $this->permissionParameters;
    }

    public function addPermissionParameter($key, $value)
    {
        $this->permissionParameters[$key] = $value;

        return $this;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function addGroup($group)
    {
        $this->groups->add($group);
    }

    public function removeGroup($group)
    {
        if ($this->groups->contains($group)) {
            $this->groups->remove($group);
        }
    }
}
