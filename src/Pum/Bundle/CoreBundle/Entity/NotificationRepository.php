<?php

namespace Pum\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Pum\Core\Extension\Notification\Entity\UserNotificationInterface;
use Pum\Core\Extension\Notification\Entity\GroupNotificationInterface;

class NotificationRepository extends EntityRepository
{
    const NOTIFICATION_CLASS = 'Pum\Bundle\CoreBundle\Entity\Notification';

    public function findByUser(UserNotificationInterface $user)
    {
        $qb = $this->createQueryBuilder('n');
        return $qb
            ->select('n')
            ->leftJoin('n.users', 'u')
            ->leftJoin('n.groups', 'g')
            ->leftJoin('g.users', 'u1')
            ->where($qb->expr()->orX(
                // Notification is associated directly to this user
                $qb->expr()->eq('u', ':user'),
                // Notification is associated to the user group
                $qb->expr()->eq('u1', ':user'),
                // Notification isn't bind to any user or group
                $qb->expr()->andX(
                    $qb->expr()->isNull('u'),
                    $qb->expr()->isNull('u1')
                )
            ))
            ->setParameters(array('user' => array($user)))
            ->getQuery()
            ->getResult();
    }

    public function findByGroup(GroupNotificationInterface $group)
    {
        $qb = $this->createQueryBuilder('n');
        return $qb
            ->select('n')
            ->leftJoin('n.users', 'u')
            ->leftJoin('n.groups', 'g')
            ->leftJoin('g.users', 'u1')
            ->where($qb->expr()->orX(
                // Notification is associated to the user group
                $qb->expr()->eq('g', ':group'),
                // Notification isn't bind to any user or group
                $qb->expr()->andX(
                    $qb->expr()->isNull('u'),
                    $qb->expr()->isNull('u1')
                )
            ))
            ->setParameters(array('group' => array($group)))
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $count = $this->createQueryBuilder('n')->select('COUNT(n.id)')->getQuery()->getSingleScalarResult();

        return $count;
    }
}
