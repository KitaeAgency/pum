<?php

namespace Pum\Core\Extension\Routing;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\PDOMySql\Driver as MysqlDriver;
use Pum\Core\Extension\Util\Namer;

class RoutingTable
{
    protected $connection;
    protected $tableName;

    public function __construct(Connection $connection, $projectName)
    {
        $this->connection = $connection;
        $this->tableName = 'routing_'.Namer::toLowercase($projectName);
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return string|null returns the matched value or null if not found
     */
    public function match($key)
    {
        try {
            $stmt = $this->connection->executeQuery(sprintf('SELECT `value` FROM %s WHERE `key` = :key', $this->tableName), array('key' => $key));
        } catch (DBALException $e) {
            $this->createTable();

            return null;
        }

        $value = $stmt->fetchColumn(0);
        if (false === $value) {
            return null;
        }

        return $value;
    }

    public function purge()
    {
        try {
            $c = $this->connection->executeQuery('DELETE FROM '.$this->tableName);
        } catch (DBALException $e) {
            $this->createTable();
        }

        return $this;
    }

    /**
     * @return RoutingTable
     */
    public function set($key, $value)
    {
        try {
            $c = $this->connection->update($this->tableName, array('value' => $value), array('`key`' => $key));
        } catch (DBALException $e) {
            echo $e;exit;
            $this->createTable();
            $c = 0;
        }

        if ($c === 0) {
            $this->connection->insert($this->tableName, array(
                '`key`' => $key,
                'value' => $value
            ));
        }

        return $this;
    }

    /**
     * @return string the created key (can be different if needed to increment)
     */
    public function add($key, $value)
    {
        $count = $this->connection->executeQuery(sprintf('SELECT COUNT(*) FROM %s WHERE `key` LIKE :key', $this->tableName), array('key' => $key.'%'))->fetchColumn(0);
        if ($count == 0) {
            $this->set($key, $value);

            return $key;
        }

        $count++; // my-stuff-2
        while (true) {
            $newKey = $key.'-'.$count;
            if (null === $this->match($newKey)) {
                $this->set($newKey, $value);

                return $newKey;
            }

            $count++;
        }
    }

    public function deleteByValue($value)
    {
        try {
            $this->connection->delete($this->tableName, array('value' => $value));
        } catch (DBALException $e) {
            $this->createTable();
        }

        return $this;
    }

    protected function createTable()
    {
        $extra = $this->connection->getDriver() instanceof MysqlDriver ? 'DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci' : '';
        $this->connection->executeQuery(sprintf('CREATE TABLE %s (
            `key` VARCHAR(128) NOT NULL,
            `value` VARCHAR(128) NOT NULL,
            PRIMARY KEY (`key`)
        )%s;', $this->tableName, $extra));
    }
}
