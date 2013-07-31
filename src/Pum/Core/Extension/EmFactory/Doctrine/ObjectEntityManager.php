<?php

namespace Pum\Core\Extension\EmFactory\Doctrine;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\Driver\PumDefinitionDriver;
use Pum\Core\Extension\EmFactory\EmFactoryExtension;
use Pum\Core\Object\ObjectFactory;
use Pum\Core\SchemaManager;

class ObjectEntityManager extends EntityManager
{
    protected $objectFactory;
    protected $schemaManager;
    protected $projectName;

    protected function setObjectFactory(ObjectFactory $objectFactory)
    {
        $this->objectFactory = $objectFactory;

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
        if (0 === strpos($name, 'obj__')) {
            return $name;
        }

        if (false === $class = $this->objectFactory->isGenerated($name)) {
            // The costful part :)
            $project    = $this->schemaManager->getProject($this->projectName);
            $definition = $this->schemaManager->getDefinition($this->projectName, $name);
            $class      = $this->objectFactory->generate($definition, $project);
        }

        return $class;
    }

    public function getObjectMetadata($name)
    {
        $class = $this->getObjectClass($name);

        return $this->getMetadataFactory()->getMetadataFor($class);
    }

    public function getObjectFactory()
    {
        return $this->objectFactory;
    }

    public function createObject($name)
    {
        $class = $this->getObjectClass($name);

        $instance = new $class();
        $instance->__pum__initialize($this->schemaManager->getConfig()->getTypeFactory());

        return $instance;
    }

    public static function createPum(EmFactoryExtension $extension, $projectName)
    {
        $schemaManager = $extension->getSchemaManager();
        $objectFactory = $schemaManager->getObjectFactory($projectName);

        // later, cache metadata here
        $cache = new ArrayCache();

        $config = Setup::createConfiguration(false, null, $cache);
        $config->setMetadataDriverImpl(new PumDefinitionDriver($schemaManager, $objectFactory, $projectName));
        $config->setClassMetadataFactoryName('Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadataFactory');
        $config->setAutoGenerateProxyClasses(true);

        $em = new ObjectEntityManager($extension->getConnection(), $config, new EventManager());
        $em->getMetadataFactory()->setSchemaManager($schemaManager);
        $em
            ->setSchemaManager($schemaManager)
            ->setObjectFactory($objectFactory)
            ->setProjectName($projectName)
        ;

        $em->getEventManager()->addEventSubscriber(new ObjectTypeInjecter($schemaManager->getConfig()->getTypeFactory()));

        return $em;
    }
}
