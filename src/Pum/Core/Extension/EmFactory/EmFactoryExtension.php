<?php

namespace Pum\Core\Extension\EmFactory;

use Doctrine\DBAL\Connection;
use Pum\Core\Extension\EmFactory\Doctrine\ObjectEntityManager;
use Pum\Core\Extension\AbstractExtension;

class EmFactoryExtension extends AbstractExtension
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
        return ObjectEntityManager::createPum($this->schemaManager, $this->connection, $projectName);
    }
}
