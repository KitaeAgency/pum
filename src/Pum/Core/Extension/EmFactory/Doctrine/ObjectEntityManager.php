<?php

namespace Pum\Core\Extension\EmFactory\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\Driver\PumDefinitionDriver;
use Pum\Core\SchemaManager;

class ObjectEntityManager extends EntityManager
{
    protected function __construct(SchemaManager $schemaManager, Connection $conn, Configuration $config, EventManager $eventManager)
    {
        $config->setClassMetadataFactoryName('Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadataFactory');

        parent::__construct($conn, $config, $eventManager);

        $this->getMetadataFactory()->setSchemaManager($schemaManager);
    }

    public function createObject($name)
    {
        $class = $this->getRepository($name)->getClassname();

        var_dump($class);exit;
    }

    public static function createPum(SchemaManager $schemaManager, Connection $conn, $projectName)
    {
        $config = Setup::createConfiguration();
        $config->setMetadataDriverImpl(new PumDefinitionDriver($schemaManager, $projectName));
        // later, cache metadata here

        return new ObjectEntityManager($schemaManager, $conn, $config, $conn->getEventManager());
    }
}
