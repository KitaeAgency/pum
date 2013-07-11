<?php

namespace Pum\Core\EventListener\Event;

use Doctrine\ORM\EntityManager;
use Pum\Core\Definition\ObjectDefinition;
use Symfony\Component\EventDispatcher\Event;

/**
 * Object used for events related to object definition.
 *
 * @see Pum\Core\Events
 */
class ObjectDefinitionEvent extends Event
{
    protected $definition;
    protected $entityManager;
    protected $className;

    public function __construct(ObjectDefinition $definition, EntityManager $entityManager, $className)
    {
        $this->definition    = $definition;
        $this->entityManager = $entityManager;
        $this->className     = $className;
    }

    public function getObjectDefinition()
    {
        return $this->definition;
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }

    public function getClassMetadata()
    {
        return $this->entityManager->getMetadataFactory()->getMetadataFor($this->className);
    }
}
