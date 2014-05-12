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
use Pum\Core\ObjectFactory;
use Pum\Core\Extension\EmFactory\Doctrine\ObjectEntityManager;
use Pum\Core\Extension\EmFactory\Doctrine\Schema\SchemaTool;

class EmFactory
{
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
     * @param ObjectFactory $objectFactory
     * @param String $projectName
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getManager(ObjectFactory $objectFactory, $projectName)
    {
        if ($projectName instanceof Project) {
            $projectName = $projectName->getName();
        }

        if (isset($this->entityManagers[$projectName])) {
            return $this->entityManagers[$projectName];
        }

        return $this->entityManagers[$projectName] = $this->createManager($objectFactory, $projectName);
    }

    private function createManager(ObjectFactory $objectFactory, $projectName)
    {
        return ObjectEntityManager::createPum($objectFactory, $this->connection, $projectName);
    }
}
