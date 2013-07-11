<?php

namespace Pum\Core\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Pum\Core\Doctrine\Metadata\ObjectClassMetadataFactory;

class ObjectEntityManager extends EntityManager
{
    protected function __construct(Connection $conn, Configuration $config, EventManager $eventManager)
    {
        $config->setClassMetadataFactoryName('Pum\Core\Doctrine\Metadata\ObjectClassMetadataFactory');

        parent::__construct($conn, $config, $eventManager);
    }

    public static function create($conn, Configuration $config, EventManager $eventManager = null)
    {
        if ( ! $config->getMetadataDriverImpl()) {
            throw ORMException::missingMappingDriverImpl();
        }

        switch (true) {
            case (is_array($conn)):
                $conn = DriverManager::getConnection(
                    $conn, $config, ($eventManager ?: new EventManager())
                );
                break;

            case ($conn instanceof Connection):
                if ($eventManager !== null && $conn->getEventManager() !== $eventManager) {
                     throw ORMException::mismatchedEventManager();
                }
                break;

            default:
                throw new \InvalidArgumentException("Invalid argument: " . $conn);
        }

        return new ObjectEntityManager($conn, $config, $conn->getEventManager());
    }
}
