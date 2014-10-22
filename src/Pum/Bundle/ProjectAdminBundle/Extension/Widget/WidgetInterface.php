<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Widget;

interface WidgetInterface
{

    public function getName();
    public function setName($name);

    public function getColor();
    public function setColor($color);

    public function getIcon();
    public function setIcon($icon);

    public function getRoute();
    public function setRoute($route, array $routeParameters);
    public function getRouteParameters();
    public function addRouteParameter($key, $value);

    public function getWeight();
    public function setWeight($weight);

    public function getPermission();
    public function setPermission($permission, array $permissionParameters);
    public function getPermissionParameters();
    public function addPermissionParameter($key, $value);

}
