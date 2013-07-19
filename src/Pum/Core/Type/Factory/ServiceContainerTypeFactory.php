<?php

namespace Pum\Core\Type\Factory;

use Pum\Core\Exception\TypeNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * TypeFactory working with a service container.
 */
class ServiceContainerTypeFactory implements TypeFactoryInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array an associative array "type name" => "service ID"
     */
    protected $serviceIds;

    public function __construct(ContainerInterface $container, array $serviceIds)
    {
        $this->container  = $container;
        $this->serviceIds = $serviceIds;
    }

    public function getType($name)
    {
        if (!$this->hasType($name)) {
            throw new TypeNotFoundException($name);
        }

        return $this->container->get($this->serviceIds[$name]);
    }

    public function hasType($name)
    {
        return isset($this->serviceIds[$name]);
    }
}
