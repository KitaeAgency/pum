<?php

namespace Pum\Core\Extension\EmFactory;

use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmFactoryExtension implements EventSubscriberInterface
{
    /**
     * DBAL connection used to create/update/delete objects.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
        );
    }

    /**
     * @param Connection $connection DBAL connection to use to create dynamic tables.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
}
