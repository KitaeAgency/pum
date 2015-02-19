<?php

namespace Pum\Core\Extension\Notification\Entity;

interface GroupNotificationInterface
{
    /**
     * Get users
     *
     * @return Transversable<UserNotificationInterface>
     */
    public function getUsers();
}
