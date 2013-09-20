<?php

namespace Pum\Extension\EmFactory;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\EventListener\Event\BeamEvent;
use Pum\Core\EventListener\Event\ProjectEvent;
use Pum\Extension\AbstractExtension;
use Pum\Core\ObjectFactory;
use Pum\Extension\EmFactory\Doctrine\ObjectEntityManager;
use Pum\Extension\EmFactory\Doctrine\Schema\SchemaTool;

class EmFactory
{
    /**
     * DBAL connection used to create/update/delete objects.
     *
     * @var Connection
     */
    protected $connection;

    protected $objectFactory;

    protected $entityManagers = array();

    /**
     * @param Connection $connection DBAL connection to use to create dynamic tables.
     */
    public function __construct(ObjectFactory $objectFactory, Connection $connection)
    {
        $this->objectFactory = $objectFactory;
        $this->connection = $connection;
    }

    public function getObjectFactory()
    {
        return $this->objectFactory;
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
        if ($projectName instanceof Project) {
            $projectName = $project->getName();
        }

        if (isset($this->entityManagers[$projectName])) {
            return $this->entityManagers[$projectName];
        }

        return $this->entityManagers[$projectName] = $this->createManager($projectName);
    }

    private function createManager($projectName)
    {
        return ObjectEntityManager::createPum($this, $projectName);
    }
}
