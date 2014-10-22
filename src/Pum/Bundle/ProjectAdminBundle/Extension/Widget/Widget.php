<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Widget;

class Widget implements WidgetInterface {
	private $name;
	private $color;
	private $icon;

	private $route;
	private $routeParameters;

	private $weight = 0;

	private $permission;
	private $permissionParameters;

	public function __construct($name, $icon = null, $color = null) {
		$this->name = $name;
		$this->icon = $icon;
		$this->color = $color;

		$this->routeParameters = array();
		$this->permissionParameters = array();
	}

	public function getName() {
		return $this->name;
	}

	public function setName( $name) {
		$this->name = $name;
		return $this;
	}

	public function getColor() {
		return $this->color;
	}

	public function setColor( $color) {
		$this->color = $color;
		return $this;
	}

	public function getIcon() {
		return $this->icon;
	}

	public function setIcon( $icon) {
		$this->icon = $icon;
		return $this;
	}

	public function getRoute() {
		return $this->route;
	}

	public function setRoute($route, array $routeParameters = array()) {
		$this->route = $route;
		foreach ($routeParameters as $key => $value) {
			$this->addRouteParameter($key, $value);
		}
		return $this;
	}

	public function getRouteParameters() {
		return $this->routeParameters;
	}

	public function addRouteParameter($key,  $value) {
		$this->routeParameters[$key] = $value;
		return $this;
	}

	public function getWeight() {
		return $this->weight;
	}

	public function setWeight( $weight) {
		$this->weight = $weight;
		return $this;
	}

	public function getPermission() {
		return $this->permission;
	}

	public function setPermission($permission, array $permissionParameters = array()) {
		$this->permission = $permission;
		foreach ($permissionParameters as $key => $value) {
			$this->addPermissionParameter($key, $value);
		}
		return $this;
	}

	public function getPermissionParameters() {
		return $this->permissionParameters;
	}

	public function addPermissionParameter($key,  $value) {
		$this->permissionParameters[$key] = $value;
		return $this;
	}
}