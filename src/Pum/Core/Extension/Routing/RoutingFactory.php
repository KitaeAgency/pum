<?php

namespace Pum\Core\Extension\Routing;

use Doctrine\DBAL\Connection;

class RoutingFactory
{
    protected $connection;
    protected $routings = array();

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getRouting($projectName)
    {
        if (!isset($this->routings[$projectName])) {
            $this->routings[$projectName] = new RoutingTable($this->connection, $projectName);
        }

        return $this->routings[$projectName];
    }
}
