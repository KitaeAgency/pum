<?php

namespace Pum\Core\Extension\EmFactory;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Pum\Core\Definition\Project;
use Pum\Core\EventListener\Event\BeamEvent;
use Pum\Core\EventListener\Event\ProjectEvent;
use Pum\Core\Extension\AbstractExtension;
use Pum\Core\Extension\EmFactory\Doctrine\ObjectEntityManager;
use Pum\Core\Extension\EmFactory\Doctrine\Schema\SchemaTool;
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
}
