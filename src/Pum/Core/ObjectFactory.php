<?php

namespace Pum\Core;

use Pum\Core\Cache\CacheInterface;
use Pum\Core\Schema\SchemaInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectFactory
{
    protected $registry;
    protected $schema;
    protected $eventDispatcher;
    protected $cache;

    public function __construct(BuilderRegistryInterface $registry, SchemaInterface $schema, CacheInterface $cache = null, EventDispatcherInterface $eventDispatcher = null)
    {
        if (null === $eventDispatcher) {
            $eventDispatcher = new EventDispatcher();
        }

        if (null === $cache) {
            $cache = new NullCache();
        }

        $this->registry        = $registry;
        $this->schema          = $schema;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createObject($projectName, $objectName)
    {
        $class = 'obj_'.md5($this->cache->getSalt().'_/é/_'.$projectName.'_\é\_'.$objectName);

        if ($this->cache->hasClass($class)) {
            $this->cache->loadClass($class);
        } else {
            $code = $this->buildClass($class, $projectName, $objectName);
            $this->cache->saveClass($class, $code);
        }

        return new $class;
    }

    private function buildClass($class, $projectName, $objectName)
    {
        $project = $this->schema->getProject($projectName);
        $object  = $project->getObject($objectName);

        $classBuilder = new ClassBuilder($class);
        foreach ($object->getFields() as $field) {
            $types          = $this->registry->getTypeHierarchy($field->getType());
            $options = $field->getOptions();

            $resolver = new OptionsResolver();
            foreach ($types as $type) {
                $type->setDefaultOptions($resolver);
                foreach ($this->registry->getTypeExtensions($type->getName()) as $typeExtension) {
                    $typeExtension->setDefaultOptions($resolver);
                }
            }
            $options = $resolver->resolve($options);

            $context = new FieldBuildContext($classBuilder, $project, $field, $options);
            foreach ($types as $type) {
                $type->buildField($context);
                foreach ($this->registry->getTypeExtensions($type->getName()) as $typeExtension) {
                    $typeExtension->buildField($context);
                }
            }
        }

        $behaviors = array_map(function ($behavior) {
            return $this->registry->getBehavior($behavior);
        }, $object->getBehaviors());

        $context = new ObjectBuildContext($classBuilder, $project, $object);
        foreach ($behaviors as $behavior) {
            $behavior->buildObject($context);
        }

        return $classBuilder->getCode();
    }
}
