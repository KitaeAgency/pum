<?php

namespace Pum\Core\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Pum\Core\Doctrine\Metadata\Driver\PumDefinitionDriver;
use Pum\Core\Manager;

class EntityManagerFactory
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Constructor.
     *
     * @param Manager $manager the PUM manager to use
     */
    public function __construct(Manager $manager, Connection $connection)
    {
        $this->manager = $manager;
        $this->connection = $connection;
    }

    public function getEntityManager()
    {
        if (null !== $this->entityManager) {
            return $this->entityManager;
        }

        $config = Setup::createConfiguration();
        $config->setMetadataDriverImpl(new PumDefinitionDriver($this->manager));
        // later, cache metadata here

        return $this->entityManager = ObjectEntityManager::create($this->connection, $config);
    }
}
