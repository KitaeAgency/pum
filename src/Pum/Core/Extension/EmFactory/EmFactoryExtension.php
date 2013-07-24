<?php

namespace Pum\Core\Extension\EmFactory;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Tools\SchemaTool;
use Pum\Core\Definition\Project;
use Pum\Core\EventListener\Event\BeamEvent;
use Pum\Core\EventListener\Event\ProjectEvent;
use Pum\Core\Extension\AbstractExtension;
use Pum\Core\Extension\EmFactory\Doctrine\ObjectEntityManager;
use Pum\Core\Extension\EmFactory\Generator\ClassGenerator;

class EmFactoryExtension extends AbstractExtension
{
    const NAME = 'em_factory';

    /**
     * DBAL connection used to create/update/delete objects.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * @var string|null
     */
    protected $cacheDir;

    protected $entityManagers = array();

    /**
     * @param Connection $connection DBAL connection to use to create dynamic tables.
     */
    public function __construct(Connection $connection, $cacheDir = null)
    {
        $this->connection = $connection;
    }

    /**
     * Returns entity manager for a given name.
     *
     * @return Doctrine\Common\Persistence\ObjectManager
     */
    public function getManager($projectName)
    {
        if (isset($this->entityManagers[$projectName])) {
            return $this->entityManagers[$projectName];
        }

        return $this->entityManagers[$projectName] = $this->createManager($projectName);
    }

    private function createManager($projectName)
    {
        return ObjectEntityManager::createPum($this->schemaManager, $this->connection, $projectName, $this->cacheDir);
    }

    public function onProjectChange(ProjectEvent $event)
    {
        $manager = $event->getSchemaManager();
        $project = $event->getProject();
        $objects = $project->getObjects();

        $this->updateSchema($project, $objects);
    }

    public function onProjectDelete(ProjectEvent $event)
    {
        $manager = $event->getSchemaManager();
        $project = $event->getProject();
        $objects = $project->getObjects();

        $this->dropSchema($project, $objects);
    }

    public function onBeamChange(BeamEvent $event)
    {
        $manager = $event->getSchemaManager();
        $beam = $event->getBeam();

        foreach ($manager->getProjectsUsingBeam($event->getBeam()) as $project) {
            $this->updateSchema($project, $beam->getObjects()->toArray());
        }
    }

    public function onBeamDelete(BeamEvent $event)
    {
        $manager = $event->getSchemaManager();
        $beam = $event->getBeam();

        foreach ($manager->getProjectsUsingBeam($beam) as $project) {
            $this->dropSchema($project, $beam->getObjects()->toArray());
        }
    }

    public function getName()
    {
        return self::NAME;
    }

    private function updateSchema(Project $project, array $objects)
    {
        $manager = $this->getManager($project->getName());
        $schemaTool = new SchemaTool($manager);

        foreach ($objects as $object) {
            $metadata = $manager->getMetadataFactory()->getMetadataFor($manager->getObjectClass($object->getName()));
            $schemaTool->updateSchema(array($metadata), true);
        }
    }

    private function dropSchema(Project $project, array $objects)
    {
        $manager = $this->getManager($project->getName());
        $schemaTool = new SchemaTool($manager);

        foreach ($objects as $object) {
            $metadata = $manager->getMetadataFactory()->getMetadataFor($manager->getObjectClass($object->getName()));
            $schemaTool->dropSchema(array($metadata));
        }
    }
}
