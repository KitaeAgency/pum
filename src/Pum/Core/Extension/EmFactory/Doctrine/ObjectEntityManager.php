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
    public function createObject($name)
    {
        $class = $this->getRepository($name)->getClassname();
    }

    public static function createPum(SchemaManager $schemaManager, Connection $conn, $projectName)
    {
        $config = Setup::createConfiguration();
        $config->setMetadataDriverImpl(new PumDefinitionDriver($schemaManager, $projectName));
        $config->setClassMetadataFactoryName('Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadataFactory');
        // later, cache metadata here

        $em = new ObjectEntityManager($conn, $config, $conn->getEventManager());
        $em->getMetadataFactory()->setSchemaManager($schemaManager);

        return $em;
    }
}
