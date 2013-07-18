<?php

namespace Pum\Core\Type\Factory;

use Pum\Core\Type\TypeInterface;
use Pum\Core\Exception\TypeNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * TypeFactory working with an array of objects.
 */
class StaticTypeFactory implements TypeFactoryInterface
{
    /**
     * @var array an associative array "type name" => Type object
     */
    protected $types;

    /**
     * Constructor.
     *
     * @param array $types An associative array (name => TypeInterface)
     */
    public function __construct(array $types = array())
    {
        $this->container  = $container;

        foreach ($types as $name => $type) {
            $this->add($name, $type);
        }
    }

    /**
     * Adds a type to the factory.
     *
     * @return StaticTypeFactory
     */
    public function add($name, TypeInterface $type)
    {
        if (isset($this->types[$name])) {
            throw new \InvalidArgumentException(sprintf('Type "%s" is already defined.'));
        }

        $this->types[$name] = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        if (!$this->hasType($name)) {
            throw new TypeNotFoundException();
        }

        return $this->types[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasType($name)
    {
        return isset($this->types[$name]);
    }
}
