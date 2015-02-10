<?php

namespace Pum\Core\Tests\Extension\Notification;


class NotificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group export
     */
    public function testCreateNotification()
    {
        $client = static::createClient();
        
        $container = $client->getContainer();
    }
}
