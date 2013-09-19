<?php

namespace Pum\Core;

use Pum\Core\Cache\CacheInterface;
use Pum\Core\ClassBuilder\ClassBuilder;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\Project;
use Pum\Core\Event\BeamEvent;
use Pum\Core\Event\ProjectEvent;
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
        $this->cache           = $cache;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getClassName($projectName, $objectName)
    {
        return 'obj_'.md5($this->cache->getSalt($projectName).'_/é/_'.$projectName.'_\é\_'.$objectName);
    }

    public function createObject($projectName, $objectName)
    {
        $class = $this->getClassName($projectName, $objectName);

        $this->loadClass($class, $projectName, $objectName);

        return new $class;
    }

    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    private function loadClass($class, $projectName, $objectName)
    {
        if (class_exists($class)) {
            return;
        }

        if ($this->cache->hasClass($class)) {
            $this->cache->loadClass($class);
        } else {
            $code = $this->buildClass($class, $projectName, $objectName);
            $this->cache->saveClass($class, $code);
        }
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

    /**
     * Saves a project (existing or new).
     */
    public function saveProject(Project $project)
    {
        $this->schema->saveProject($project);

        $this->cache->clear($project->getName());
        $this->eventDispatcher->dispatch(Events::PROJECT_CHANGE, new ProjectEvent($project, $this));
    }

    /**
     * Saves a beam (existing or new).
     */
    public function saveBeam(Beam $beam)
    {
        $this->schema->saveBeam($beam);

        foreach ($this->getProjectsUsingBeam($beam) as $project) {
            $this->cache->clear($project->getName());
        }

        $this->eventDispatcher->dispatch(Events::BEAM_CHANGE, new BeamEvent($beam, $this));
    }

    /**
     * Deletes a project (existing or new).
     */
    public function deleteProject(Project $project)
    {
        $this->cache->clear($project->getName());

        $this->eventDispatcher->dispatch(Events::PROJECT_DELETE, new ProjectEvent($project, $this));
        $this->schema->deleteProject($project);
    }

    /**
     * Deletes a beam (existing or new).
     */
    public function deleteBeam(Beam $beam)
    {
        foreach ($this->getProjectsUsingBeam($beam) as $project) {
            $this->cache->clear($project->getName());
        }

        $this->eventDispatcher->dispatch(Events::BEAM_DELETE, new BeamEvent($beam, $this));

        $this->schema->deleteBeam($beam);
    }

    /**
     * @return array
     */
    public function getProjectsUsingBeam(Beam $beam)
    {
        $result = array();

        foreach ($this->getAllProjects() as $project) {
            if ($project->hasBeam($beam)) {
                $result[] = $project;
            }
        }

        return $result;
    }

    /**
     * Returns definition of an object.
     *
     * @param string $name name of the definition to fetch
     *
     * @return ObjectDefinition
     */
    public function getDefinition($projectName, $name)
    {
        $project = $this->schema->getProject($projectName);

        return $project->getObject($name);
    }
    /**
     * @return Beam
     */
    public function getBeam($name)
    {
        return $this->schema->getBeam($name);
    }

    /**
     * @return Project
     */
    public function getProject($name)
    {
        return $this->schema->getProject($name);
    }

    /**
     * Returns all beams.
     *
     * @return array
     */
    public function getAllBeams()
    {
        $result = array();
        foreach ($this->schema->getBeamNames() as $name) {
            $result[] = $this->schema->getBeam($name);
        }

        return $result;
    }
    /**
     * Returns all projects.
     *
     * @return array
     */
    public function getAllProjects()
    {
        $result = array();
        foreach ($this->schema->getProjectNames() as $name) {
            $result[] = $this->schema->getProject($name);
        }

        return $result;
    }
}
