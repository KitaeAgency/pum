<?php

namespace Pum\Core\Extension\EmFactory;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\EventListener\Event\BeamEvent;
use Pum\Core\EventListener\Event\ProjectEvent;
use Pum\Core\Extension\AbstractExtension;
use Pum\Core\Extension\EmFactory\Doctrine\ObjectEntityManager;
use Pum\Core\Extension\EmFactory\Doctrine\Schema\SchemaTool;
use Pum\Core\Object\ObjectFactory;

class EmFactoryExtension extends AbstractExtension
{
    const NAME = 'em_factory';

    /**
     * DBAL connection used to create/update/delete objects.
     *
     * @var Connection
     */
    protected $connection;

    protected $entityManagers = array();

    /**
     * @param Connection $connection DBAL connection to use to create dynamic tables.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
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
        return ObjectEntityManager::createPum($this, $projectName);
    }

    public function onProjectChange(ProjectEvent $event)
    {
        $manager = $event->getSchemaManager();
        $project = $event->getProject();

        $this->updateSchema($project, new NullLogger());
    }

    public function onProjectDelete(ProjectEvent $event)
    {
        $manager = $event->getSchemaManager();
        $project = $event->getProject();
        $objects = $project->getObjects();

        $this->updateSchema($project, new NullLogger());
    }

    public function onBeamChange(BeamEvent $event)
    {
        $manager = $event->getSchemaManager();
        $beam = $event->getBeam();

        foreach ($manager->getProjectsUsingBeam($beam) as $project) {
            $this->updateSchema($project, new NullLogger());
        }
    }

    public function onBeamDelete(BeamEvent $event)
    {
        $manager = $event->getSchemaManager();
        $beam = $event->getBeam();

        foreach ($manager->getProjectsUsingBeam($beam) as $project) {
            $this->updateSchema($project, new NullLogger());
        }
    }

    public function getName()
    {
        return self::NAME;
    }

    public function updateSchema(Project $project, LoggerInterface $logger)
    {
        $manager = $this->getManager($project->getName());

        $tool = new SchemaTool($project, $manager);
        $tool->update($logger);
    }

    /**
     * Returns schema tables for a given object definition (entity + relations?)
     */
    public function getSchemaTables(Project $project, ObjectDefinition $definition)
    {
        $em         = $this->getManager($project->getName());
        $metadata   = $em->getObjectMetadata($definition->getName());
        $tableNames = array($metadata->getTableName());
        $tableNames = array_merge($tableNames, $metadata->getAdditionalTables());

        $conn = $this->getConnection();

        return array_map(function ($tableName) use ($conn) {
            return $conn->getSchemaManager()->listTableDetails($tableName);
        }, $tableNames);
    }
}
