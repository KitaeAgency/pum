<?php

namespace Pum\Core\Extension\Notification\Entity;

interface UserNotificationInterface {
    /*
     * @return string
     */
    public function getEmail();

    /*
     * @return \DateTime
     */
    public function getLastNotification();
}
