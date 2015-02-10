<?php

namespace Pum\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pum\Core\Extension\Notification\Entity\UserNotificationInterface;

/** 
 * @ORM\MappedSuperclass
 */
abstract class UserNotification
{
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_notification;

    /**
     * Set last_notification
     *
     * @param \DateTime $lastNotification
     * @return UserNotification
     */
    public function setLastNotification($lastNotification)
    {
        $this->last_notification = $lastNotification;

        return $this;
    }

    /**
     * Get last_notification
     *
     * @return \DateTime 
     */
    public function getLastNotification()
    {
        return $this->last_notification;
    }
}
