<?php

namespace Pum\Extension\EmFactory\Doctrine;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Pum\Core\EventListener\Event\ObjectEvent;
use Pum\Core\Events;
use Pum\Core\ObjectFactory;
use Pum\Extension\EmFactory\Doctrine\Listener\ObjectLifecycleListener;
use Pum\Extension\EmFactory\Doctrine\Metadata\Driver\PumDefinitionDriver;
use Pum\Extension\EmFactory\Doctrine\Schema\SchemaTool;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ObjectEntityManager extends EntityManager
{
    protected $objectFactory;
    protected $objectEventDispatcher;
    protected $projectName;

    /**
     * @return ObjectEntityManager
     */
    protected function setObjectFactory(ObjectFactory $objectFactory)
    {
        $this->objectFactory = $objectFactory;
        $this->objectEventDispatcher = $objectFactory->getEventDispatcher();

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
        if ($this->objectFactory->isProjectClass($name)) {
            return $name;
        }

        return $this->objectFactory->getClassName($this->projectName, $name);
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

    /**
     * @return EventDispatcherInterface
     */
    public function getObjectEventDispatcher()
    {
        return $this->objectEventDispatcher;
    }

    /**
     * @return Object
     */
    public function createObject($name)
    {
        $instance = $this->objectFactory->createObject($this->projectName, $name);
        $this->getObjectEventDispatcher()->dispatch(Events::OBJECT_CREATE, new ObjectEvent($instance));

        return $instance;
    }

    public static function createPum(ObjectFactory $objectFactory, Connection $connection, $projectName)
    {
        // later, cache metadata here
        $cache = new ArrayCache();
        $config = Setup::createConfiguration(false, null, $cache);
        $config->setMetadataDriverImpl(new PumDefinitionDriver($objectFactory));
        $config->setAutoGenerateProxyClasses(true);

        $eventManager = new EventManager();
        $eventManager->addEventSubscriber(new ObjectLifecycleListener($objectFactory->getEventDispatcher()));

        $em = new ObjectEntityManager($connection, $config, $eventManager);
        $em
            ->setObjectFactory($objectFactory)
            ->setProjectName($projectName)
        ;

        return $em;
    }

    public function updateSchema()
    {
        $tool = new SchemaTool($this->objectFactory->getProject($this->projectName), $this);
        $tool->update();
    }

    /**
     * Returns schema tables for a given object definition (entity + relations?)
     */
    public function getSchemaTables(Project $project, ObjectDefinition $definition)
    {
        $em         = $this->getManager($project->getName());
        $metadata   = $em->getObjectMetadata($definition->getName());
        $tableNames = array($metadata->getTableName());
        $tableNames = array_merge($tableNames, $metadata->getAdditionalTables());

        $conn = $this->getConnection();

        return array_map(function ($tableName) use ($conn) {
            return $conn->getSchemaManager()->listTableDetails($tableName);
        }, $tableNames);
    }
}
