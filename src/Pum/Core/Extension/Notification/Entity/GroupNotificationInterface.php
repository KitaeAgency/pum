<?php

namespace Pum\Core\Extension\Notification\Entity;

interface GroupNotificationInterface {
    /*
     * @return Transversable<UserNotificationInterface>
     */
    public function getUsers();
}
