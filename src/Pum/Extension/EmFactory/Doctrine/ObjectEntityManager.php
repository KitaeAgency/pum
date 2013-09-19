<?php

namespace Pum\Core\Extension\EmFactory\Doctrine;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Pum\Core\EventListener\Event\ObjectEvent;
use Pum\Core\Events;
use Pum\Core\Extension\EmFactory\Doctrine\Listener\ObjectLifecycleListener;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\Driver\PumDefinitionDriver;
use Pum\Core\Extension\EmFactory\EmFactoryExtension;
use Pum\Core\Object\ObjectFactory;
use Pum\Core\SchemaManager;
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

        return $this;
    }

    /**
     * @return ObjectEntityManager
     */
    protected function setObjectEventDispatcher(EventDispatcherInterface $objectEventDispatcher)
    {
        $this->objectEventDispatcher = $objectEventDispatcher;

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
        if (0 === strpos($name, ObjectFactory::CLASS_PREFIX)) {
            return $name;
        }

        return $this->objectFactory->getClass($name);
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
        $instance = $this->objectFactory->createObject($name);
        $this->getObjectEventDispatcher()->dispatch(Events::OBJECT_CREATE, new ObjectEvent($instance));

        return $instance;
    }

    public static function createPum(EmFactoryExtension $extension, $projectName)
    {
        $schemaManager = $extension->getSchemaManager();
        $objectFactory = $schemaManager->getObjectFactory($projectName);

        // later, cache metadata here
        $cache = new ArrayCache();

        $config = Setup::createConfiguration(false, null, $cache);
        $config->setMetadataDriverImpl(new PumDefinitionDriver());
        $config->setClassMetadataFactoryName('Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadataFactory');
        $config->setAutoGenerateProxyClasses(true);

        $eventManager = new EventManager();
        $eventManager->addEventSubscriber(new ObjectLifecycleListener($schemaManager->getEventDispatcher()));

        $em = new ObjectEntityManager($extension->getConnection(), $config, $eventManager);
        $em
            ->setObjectFactory($objectFactory)
            ->setObjectEventDispatcher($schemaManager->getEventDispatcher())
            ->setProjectName($projectName)
        ;

        return $em;
    }
}
