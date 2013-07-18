<?php

namespace Pum\Core\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Pum\Core\Doctrine\Metadata\Driver\PumDefinitionDriver;
use Pum\Core\Doctrine\Metadata\ObjectClassMetadataFactory;
use Pum\Core\Manager;

class ObjectEntityManager extends EntityManager
{
    protected function __construct(Manager $manager, Connection $conn, Configuration $config, EventManager $eventManager)
    {
        $config->setClassMetadataFactoryName('Pum\Core\Doctrine\Metadata\ObjectClassMetadataFactory');

        parent::__construct($conn, $config, $eventManager);

        $this->getMetadataFactory()->setManager($manager);
    }

    public static function createPum(Manager $manager, Connection $conn)
    {
        $config = Setup::createConfiguration();
        $config->setMetadataDriverImpl(new PumDefinitionDriver($manager));
        // later, cache metadata here

        return new ObjectEntityManager($manager, $conn, $config, $conn->getEventManager());
    }
}
