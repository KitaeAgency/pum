<?php

namespace Pum\Core\Extension\EmFactory\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\Driver\PumDefinitionDriver;
use Pum\Core\Extension\EmFactory\Generator\ClassGenerator;
use Pum\Core\SchemaManager;

class ObjectEntityManager extends EntityManager
{
    protected $classGenerator;
    protected $schemaManager;
    protected $projectName;

    protected function setClassGenerator(ClassGenerator $classGenerator)
    {
        $this->classGenerator = $classGenerator;

        return $this;
    }

    public function setSchemaManager(SchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;

        return $this;
    }

    public function setProjectName($projectName)
    {
        $this->projectName = $projectName;

        return $this;
    }

    public function createObject($name)
    {
        if (false === $class = $this->classGenerator->isGenerated($name)) {
            $class = $this->classGenerator->generate($this->schemaManager->getDefinition($this->projectName, $name));
        }

        return new $class();
    }

    public static function createPum(SchemaManager $schemaManager, Connection $conn, $projectName, $cacheDir = null)
    {
        $config = Setup::createConfiguration();
        $config->setMetadataDriverImpl(new PumDefinitionDriver($schemaManager, $projectName));
        $config->setClassMetadataFactoryName('Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadataFactory');
        // later, cache metadata here

        $em = new ObjectEntityManager($conn, $config, $conn->getEventManager());
        $em->getMetadataFactory()->setSchemaManager($schemaManager);
        $em
            ->setSchemaManager($schemaManager)
            ->setClassGenerator(new ClassGenerator($projectName, $cacheDir))
            ->setProjectName($projectName)
        ;

        return $em;
    }
}
