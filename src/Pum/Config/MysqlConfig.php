<?php

namespace Pum\Config;

use Doctrine\DBAL\Connection;


class MysqlConfig implements ConfigInterface
{
    const CONFIG_TABLE_NAME = 'pum_config';

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
    * APC Key
    *
    * @var string
    */
    private $apcKey;

    public function __construct(Connection $connection, $apcKey)
    {
        $this->connection = $connection;
        $this->apcKey     = $apcKey;
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
    public function set($key, $value)
    {
        $this->values = array_merge($this->all(), array($key => $value));
    }

    /**
    * {@inheritDoc}
    */
    public function remove($key)
    {
        unset($this->values[$key]);
    }

    /**
    * {@inheritDoc}
    */
    public function clear()
    {
        if ($this->useCache()) {
            return $this->apcClear();
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
    * Save current values to configuration.
    *
    * @return boolean
    */
    public function flush()
    {
        $this->runSQL('DELETE FROM `'.self::CONFIG_TABLE_NAME.'`');

        foreach ($this->values as $key => $value) {
            $this->runSQL('INSERT INTO '.self::CONFIG_TABLE_NAME.' (`key`, `value`) VALUES ('.$this->connection->quote($key).','.$this->connection->quote(json_encode($value)).');');
        }

        if ($this->useCache()) {
            return $this->apcStore($this->apcKey, $this->values);
        }

        return true;
    }

    /**
    * Read all values from configuration.
    *
    * @return array All values
    */
    private function restore()
    {
        $values = false;

        if ($this->useCache()) {
            $values = $this->apcFetch($this->apcKey);
        }

        if($values === false) {
            return $this->rawRestore();
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
        $stmt = $this->runSql('SELECT `key`, `value` FROM `'. self::CONFIG_TABLE_NAME .'`');

        $values = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $values[$row['key']] = json_decode($row['value']);
        }

        return $values;
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
            $this->connection->executeQuery(sprintf('CREATE TABLE %s (`key` VARCHAR(40), `value` TEXT);', self::CONFIG_TABLE_NAME));
        }

        return $this->connection->executeQuery($query, $parameters);
    }

    /**
    * Detect if APC is enabled
    *
    * @return boolean
    */
    private function hasApc()
    {
        return extension_loaded('apc') && ini_get('apc.enabled');
    }

    /**
    *  Fetch a stored variable from the cache
    */
    private function apcFetch($key)
    {
        return apc_fetch($key);
    }

    /**
    * Cache a variable in the data store
    */
    private function apcStore($key, $values, $ttl = 0)
    {
        return (bool) apc_store($key, $values, (int) $ttl);
    }

    /**
    * Clear Cache
    */
    private function apcClear()
    {
        return apc_clear_cache();
    }

    /**
    * Clear Cache
    */
    private function useCache()
    {
        return $this->hasApc();
    }
}
