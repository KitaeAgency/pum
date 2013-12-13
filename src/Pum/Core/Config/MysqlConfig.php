<?php

namespace Pum\Core\Config;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;

class MysqlConfig implements ConfigInterface
{
    const CONFIG_TABLE_NAME = 'pum_config';
    const APC_NAMESPACE     = 'pum_config';

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
    * APC Enabled
    *
    * @var string
    */
    private $apcCacheDriver;

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

        if (extension_loaded('apc')) {
            $this->apcCacheDriver = new \Doctrine\Common\Cache\ApcCache();
            $this->apcCacheDriver->setNamespace(self::APC_NAMESPACE);
        } else {
            $this->apcCacheDriver = null;
        }
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
        if (null !== $this->apcCacheDriver) {
            $this->apcCacheDriver->delete($this->apcKey);
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

        $this->apcSave($this->values);

        return true;
    }

    /**
    * Read all values from configuration.
    *
    * @return array All values
    */
    private function restore()
    {
        $values = $this->apcFetch($this->apcKey);

        if(!$values) {
            $this->apcSave($values = $this->rawRestore());
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
            $extra = $this->connection->getDriver() instanceof SqliteDriver ? ';' : ' DEFAULT CHARSET = utf8 COLLATE = utf8_unicode_ci;';
            $this->connection->executeQuery(sprintf('CREATE TABLE %s (`key` VARCHAR(64), `value` TEXT, PRIMARY KEY (`key`))'.$extra, self::CONFIG_TABLE_NAME));
        }

        return $this->connection->executeQuery($query, $parameters);
    }

    private function apcSave($values)
    {
        if (null !== $this->apcCacheDriver) {
            $this->clear();
            $this->apcCacheDriver->save($this->apcKey, $values);
        }
    }

    private function apcFetch()
    {
        if (null !== $this->apcCacheDriver) {
            if ($this->apcCacheDriver->contains($this->apcKey)) {
                return $this->apcCacheDriver->fetch($this->apcKey);
            } else {
                return false;
            }
        }

        return false;
    }
}
