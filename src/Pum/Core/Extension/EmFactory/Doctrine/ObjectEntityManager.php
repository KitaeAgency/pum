<?php

namespace Pum\Core\Extension\EmFactory\Doctrine;

use Doctrine\Common\Cache\ArrayCache;
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

    public function getRepository($entityName)
    {
        return parent::getRepository($this->getObjectClass($entityName));
    }

    public function getObjectClass($name)
    {
        if (false === $class = $this->classGenerator->isGenerated($name)) {
            $class = $this->classGenerator->generate($this->schemaManager->getDefinition($this->projectName, $name));
        }

        return $class;
    }

    public function getObjectMetadata($name)
    {
        $class = $this->getObjectClass($name);

        return $this->getMetadataFactory()->getMetadataFor($class);
    }

    public function createObject($name)
    {
        $class = $this->getObjectClass($name);

        $instance = new $class();
        $instance->__pum__setTypes($this->schemaManager->getConfig()->getTypeFactory());

        return $instance;
    }

    public static function createPum(SchemaManager $schemaManager, Connection $conn, $projectName, $cacheDir = null)
    {
        $classGenerator = new ClassGenerator($projectName, $cacheDir);

        // later, cache metadata here
        $cache = new ArrayCache();

        $config = Setup::createConfiguration(false, null, $cache);
        $config->setMetadataDriverImpl(new PumDefinitionDriver($schemaManager, $classGenerator, $projectName));
        $config->setClassMetadataFactoryName('Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadataFactory');
        $config->setAutoGenerateProxyClasses(true);

        $em = new ObjectEntityManager($conn, $config, new EventManager());
        $em->getMetadataFactory()->setSchemaManager($schemaManager);
        $em
            ->setSchemaManager($schemaManager)
            ->setClassGenerator($classGenerator)
            ->setProjectName($projectName)
        ;

        $em->getEventManager()->addEventSubscriber(new ObjectTypeInjecter($schemaManager->getConfig()->getTypeFactory()));

        return $em;
    }
}
