<?php

namespace Pum\Core\Extension\Notification\Entity;

interface UserNotificationInterface
{

    /**
     * Get fullname
     *
     * @return string
     */
    public function getFullname();


    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Get last notification view
     *
     * @return \DateTime
     */
    public function getLastNotification();
}
