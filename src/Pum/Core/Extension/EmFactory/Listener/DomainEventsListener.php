<?php

namespace Pum\Core\Extension\EmFactory\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Pum\Core\Definition\EventObject;
use Pum\Core\Extension\EmFactory\Doctrine\ObjectEntityManager;
use Pum\Core\ObjectFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DomainEventsListener implements EventSubscriber
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array('onFlush');
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $objectFactory = $this->container->get('pum');
        $uow           = $args->getEntityManager()->getUnitOfWork();

        foreach ($uow->getIdentityMap() as $class => $entities) {
            foreach ($entities as $entity) {
                $this->process($objectFactory, $entity);
            }
        }
    }

    private function process(ObjectFactory $objectFactory, $entity)
    {
        if (!$entity instanceof EventObject) {
            return;
        }

        foreach($entity->popEvents() as $row) {
            list($name, $event) = $row;
            $event->setObjectFactory($objectFactory);
            $objectFactory->getEventDispatcher()->dispatch($name, $event);
        }
    }
}
