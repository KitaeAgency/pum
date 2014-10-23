<?php

namespace Pum\Core\Extension\EmFactory\Doctrine;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\Event\ObjectEvent;
use Pum\Core\Events;
use Pum\Core\ObjectFactory;
use Pum\Core\Extension\EmFactory\Doctrine\Listener\ObjectLifecycleListener;
use Pum\Core\Extension\EmFactory\Doctrine\Schema\SchemaTool;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ObjectEntityManager extends EntityManager
{
    protected $objectFactory;
    protected $objectEventDispatcher;
    protected $projectName;

    /**
     * @param ObjectFactory $objectFactory
     * @return ObjectEntityManager
     */
    protected function setObjectFactory(ObjectFactory $objectFactory)
    {
        $this->objectFactory = $objectFactory;
        $this->objectEventDispatcher = $objectFactory->getEventDispatcher();

        return $this;
    }

    /**
     * @param String $projectName
     * @return $this
     */
    public function setProjectName($projectName)
    {
        $this->projectName = $projectName;

        return $this;
    }

    /**
     * @param string $entityName
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository($entityName)
    {
        return parent::getRepository($this->getObjectClass($entityName));
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getObjectClass($name)
    {
        if ($this->objectFactory->isProjectClass($name)) {
            return $name;
        }

        return $this->objectFactory->getClassName($this->projectName, $name);
    }

    /**
     * @param string $name
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    public function getObjectMetadata($name)
    {
        $class = $this->getObjectClass($name);

        return $this->getMetadataFactory()->getMetadataFor($class);
    }

    /**
     * @return ObjectFactory
     */
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
     * @param $name
     * @return mixed
     */
    public function createObject($name)
    {
        $instance = $this->objectFactory->createObject($this->projectName, $name);
        $this->getObjectEventDispatcher()->dispatch(Events::OBJECT_CREATE, new ObjectEvent($instance, $this->objectFactory));

        return $instance;
    }

    /**
     * @param ObjectFactory $objectFactory
     * @param Connection $connection
     * @param $projectName
     * @return ObjectEntityManager
     */
    public static function createPum(ObjectFactory $objectFactory, Connection $connection, Configuration $config, $projectName)
    {
        $eventManager = new EventManager();
        $eventManager->addEventSubscriber(new ObjectLifecycleListener($objectFactory));

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

    public function clearCache()
    {
        $this->getConfiguration()->getMetadataCacheImpl()->deleteAll();
        $this->getConfiguration()->getQueryCacheImpl()->deleteAll();
        $this->getConfiguration()->getResultCacheImpl()->deleteAll();
    }

    /**
     * Returns schema tables for a given object definition (entity + relations?)
     *
     * @param Project $project
     * @param ObjectDefinition $definition
     * @return array
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
