<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Widget;

class Widget implements WidgetInterface
{
    const DEFAULT_ICON   = 'settings2';
    const DEFAULT_COLOR  = 'concrete';
    const DEFAULT_WEIGHT = 20;

    private $name;
    private $label;
    private $color;
    private $icon;
    private $weight;
    private $uid;

    private $route;
    private $routeParameters;

    private $permission;
    private $permissionParameters;

    public function __construct($name, $icon = null, $color = null, $weight = null, $uid = null)
    {
        $this->setName($name);
        $this->setLabel($name);
        $this->setIcon($icon);
        $this->setColor($color);
        $this->setWeight($weight);
        $this->setUid($uid);

        $this->routeParameters      = array();
        $this->permissionParameters = array();
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

    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function setUid($uid)
    {
        $this->uid = (null !== $uid) ? $uid : md5(uniqid().time());

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

}
