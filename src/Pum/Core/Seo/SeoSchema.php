<?php

namespace Pum\Core\Seo;

use Pum\Core\ObjectFactory;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Exception\DefinitionNotFoundException;
use Pum\Bundle\CoreBundle\PumContext;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A SeoSchema.
 */
class SeoSchema 
{
    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var PumContext
     */
    protected $context;

    /**
     * @var ArrayCollection
     */
    protected $objects;

    /**
     * Constructor.
     */
    public function __construct(ObjectFactory $objectFactory, PumContext $context)
    {
        $this->objectFactory = $objectFactory;
        $this->context       = $context;
        $this->createSeoSchemaFromContext();
    }

    /**
     * Tests if beam has an object with given name.
     *
     * @param string $name name of object
     *
     * @return boolean
     */
    public function hasObject($name)
    {
        foreach ($this->getObjects() as $object) {
            if ($object->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return ObjectDefinition
     *
     * @throws DefinitionNotFoundException
     */
    public function getObject($name)
    {
        foreach ($this->getObjects() as $object) {
            if ($object->getName() === $name) {
                return $object;
            }
        }

        throw new DefinitionNotFoundException($name);
    }

    /**
     * @return SeoSchema
     */
    public function addObject(ObjectDefinition $definition)
    {
        if ($this->hasObject($definition->getName())) {
            throw new \RuntimeException(sprintf('Object "%s" is already present in Seo Schema.', $definition->getName()));
        }

        $this->getObjects()->add($definition);

        return $this;
    }

    /**
     * @return SeoSchema
     */
    public function removeObject(ObjectDefinition $definition)
    {
        $this->getObjects()->removeElement($definition);

        return $this;
    }

    /**
     * @return array
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * Import Seo Schema from Context
     */
    private function createSeoSchemaFromContext()
    {
        $this->objects  = new ArrayCollection();
        $orderedObjects = array();

        foreach ($this->context->getAllProjects() as $project) {
            foreach ($project->getBeams() as $beam) {
                foreach ($beam->getObjects() as $object) {
                    if ($object->isSeoEnabled()) {
                        $orderedObjects[intval($object->getSeoOrder())][] = $object;
                    }
                }
            }
        }
        ksort($orderedObjects);

        foreach ($orderedObjects as $objects) {
            foreach ($objects as $object) {
                if (!$this->hasObject($object->getName())) {
                    $this->addObject($object);
                }
            }
        }
    }

    /**
     * Store Seo Schema into Objects
     */
    public function saveSeoSchema()
    {
        $beams = array();
        
        foreach ($this->getObjects() as $object) {
            $beams[] = $object->getBeam()->getName();
        }

        foreach (array_unique($beams) as $beamName) {
            $this->objectFactory->saveBeam($this->objectFactory->getBeam($beamName));
        }
    }
}
