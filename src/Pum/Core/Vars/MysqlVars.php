<?php

namespace Pum\Core\Vars;

use Pum\Core\Extension\Util\Namer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;

class MysqlVars implements VarsInterface
{
    const VARS_TABLE_NAME = 'vars_';

    /**
    * Config values
    *
    * @var array
    */
    private $values;

    /**
    * DBAL Connection
    *
    * @var Connection
    */
    private $connection;

    /**
    * TableName
    *
    * @var string
    */
    private $tableName;

    /**
    *
    * @var string
    */
    private $cache;

    public function __construct(Connection $connection, $projectName, $cache = null)
    {
        $this->connection  = $connection;
        $this->tableName   = self::VARS_TABLE_NAME.Namer::toLowercase($projectName);
        $this->cache       = $cache;
    }

    /**
    * {@inheritDoc}
    */
    public function getValue($key, $default = null)
    {
        $values = $this->all();

        if (!isset($values[$key])) {
            return $default;
        }

        $var = $values[$key];

        switch ($var['type']) {
            case 'boolean':
                $value = (bool)$var['value'];
                break;

            case 'integer':
                $value = (int)$var['value'];
                break;

            default:
                $value = (string)$var['value'];
                break;
        }

        return $value;
    }

    /**
    * {@inheritDoc}
    */
    public function get($key, $default = null)
    {
        $values = $this->all();

        if (!isset($values[$key])) {
            return $default;
        }

        return $values[$key];
    }

    /**
    * {@inheritDoc}
    */
    public function set($key, $value, $type = 'string', $description = null)
    {
        $var = array(
            'key'         => $key,
            'value'       => $value,
            'type'        => $type,
            'description' => $description
        );

        $this->values = array_merge($this->all(), array($key => $var));
    }

    /**
    * {@inheritDoc}
    */
    public function remove($key)
    {
        if (null === $this->values) {
            $this->values = $this->restore();
        }

        unset($this->values[$key]);
    }

    /**
    * {@inheritDoc}
    */
    public function clear()
    {
        if (null !== $this->cache) {
            $this->cache->delete($this->tableName);
        }

        return true;
    }

    /**
    * {@inheritDoc}
    */
    public function all()
    {
        if (null !== $this->values) {
            return $this->values;
        }

        return $this->values = $this->restore();
    }

    /**
    * Save current values to vars.
    *
    * @return boolean
    */
    public function flush()
    {
        $this->runSQL('DELETE FROM `'.$this->tableName.'`');

        foreach ($this->values as $key => $var) {
            $value       = $var['value'];
            $type        = $var['type'];
            $description = $var['description'];

            $this->runSQL('INSERT INTO '.$this->tableName.' (`key`, `type`, `value`, `description`) VALUES ('.$this->connection->quote($key).','.$this->connection->quote($type).','.$this->connection->quote(json_encode($value)).','.$this->connection->quote($description).');');
        }

        $this->refresh();

        return true;
    }

    /**
    * Save key to vars.
    *
    * @return boolean
    */
    public function save($var)
    {
        $values = $this->all();

        $key         = $var['key'];
        $value       = $var['value'];
        $type        = $var['type'];
        $description = $var['description'];

        if (!isset($values[$key])) {
            $this->runSQL('INSERT INTO '.$this->tableName.' (`key`, `type`, `value`, `description`) VALUES ('.$this->connection->quote($key).','.$this->connection->quote($type).','.$this->connection->quote(json_encode($value)).','.$this->connection->quote($description).');');
        } else {
            $this->runSQL('UPDATE '.$this->tableName.' SET `type` = '.$this->connection->quote($type).', `value` = '.$this->connection->quote(json_encode($value)).', `description` = '.$this->connection->quote($description).' WHERE `key` = '.$this->connection->quote($key).';');
        }

        $this->refresh();

        return true;
    }

    /**
    * Delete key from vars.
    *
    * @return boolean
    */
    public function delete($key)
    {
        $this->runSQL('DELETE FROM '.$this->tableName.' WHERE `key` = '.$this->connection->quote($key).';');

        $this->refresh();

        return true;
    }

    /**
    * Read all values from configuration.
    *
    * @return array All values
    */
    private function restore()
    {
        if(null === $this->cache) {
            $values = $this->rawRestore();
        }

        return $values;
    }

    /**
    * Read all values from configuration with no cache.
    *
    * @return array All values
    */
    private function rawRestore()
    {
        $stmt = $this->runSql('SELECT * FROM `'. $this->tableName .'`');

        $values = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $values[$row['key']] = array(
                'key'         => $row['key'],
                'type'        => $row['type'],
                'value'       => json_decode($row['value']),
                'description' => $row['description']
            );
        }

        return $values;
    }

    /**
    * refresh vars and cache.
    *
    * @return array All values
    */
    private function refresh()
    {
        $this->values = null;
        $this->clear();
    }

    /**
    * Proxy method to connection object. If an error occurred because of unfound table, tries to create table and rerun request.
    *
    * @param string $query SQL query
    * @param array $parameters query parameters
    */
    private function runSQL($query, array $parameters = array())
    {
        try {
            return $this->connection->executeQuery($query, $parameters);
        } catch (\Exception $e) {
            $isSqliteDriver = $this->connection->getDriver() instanceof SqliteDriver ? true : false;

            if ($isSqliteDriver) {
                $this->connection->executeQuery(sprintf('CREATE TABLE %s (`key` VARCHAR(64), `value` VARCHAR(512), `description` VARCHAR(512), `type` VARCHAR(10), PRIMARY KEY (`key`))'.';', $this->tableName));
            } else {
                $this->connection->executeQuery(sprintf('CREATE TABLE %s (`id` INT AUTO_INCREMENT,`key` VARCHAR(64) UNIQUE, `value` VARCHAR(512), `description` VARCHAR(512), `type` VARCHAR(10), PRIMARY KEY (`id`))'.'DEFAULT CHARSET = utf8 COLLATE = utf8_unicode_ci;', $this->tableName));
            }
        }

        return $this->connection->executeQuery($query, $parameters);
    }
}
