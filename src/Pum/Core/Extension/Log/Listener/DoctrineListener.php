<?php

namespace Pum\Core\Extension\Log\Listener;

use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\Common\EventSubscriber;

use Pum\Core\Extension\Log\Service\LogService;
use Pum\Bundle\CoreBundle\Entity\Log;

class DoctrineListener implements EventSubscriber
{
    /**
     * @var Doctrine\ORM\EntityManager $em
     */
    protected $em = null;

    /**
     * @var Doctrine\ORM\UnitOfWork $uow
     */
    protected $uow = null;

    /**
     * @var Pum\Core\Extension\Log\Service\LogService $logService
     */
    protected $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    public function getSubscribedEvents()
    {
        return array(
            Events::onFlush => 'onFlush'
        );
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        if ($this->logService->getEnabled()) {
            $this->em = $eventArgs->getEntityManager();
            $this->uow = $this->em->getUnitOfWork();

            // Turn the listener off so flush a log entity won't call back this listener
            $eventManager = $this->em->getEventManager();
            $eventManager->removeEventListener('onFlush', $this);

            $logs = array();

            // Insertions
            foreach ($this->uow->getScheduledEntityInsertions() as $entity) {
                if (($log = $this->logService->create(
                    $entity,
                    Log::EVENT_CREATE,
                    array('em' => $this->em)
                ))) {
                    $logs[] = $log;
                }
            }

            // Updates
            foreach ($this->uow->getScheduledEntityUpdates() as $entity) {
                $changes = $this->logService->getChanges($this->uow->getEntityChangeSet($entity));

                if (($log = $this->logService->create(
                    $entity,
                    Log::EVENT_UPDATE,
                    array('em' => $this->em)
                ))) {
                    $logs[] = $log;
                }
            }

            // Deletions
            foreach ($this->uow->getScheduledEntityDeletions() as $entity) {
                if (($log = $this->logService->create(
                    $entity,
                    Log::EVENT_DELETE
                ))) {
                    $logs[] = $log;

                }
            }

            if (!empty($logs)) {
                $this->em->transactional(function($em) use ($logs) {
                    foreach ($logs as $log) {
                        $em->persist($log);
                    }
                    $em->flush();
                });
            }

            // Turn the listener back on
            $eventManager->addEventListener('onFlush', $this);
        }
    }
}
