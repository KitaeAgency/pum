<?php

namespace Pum\Core\Config;

use Doctrine\Common\Cache;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;

class MysqlConfig implements ConfigInterface
{
    const CONFIG_TABLE_NAME = 'pum_config';
    const CONFIG_NAMESPACE  = 'pum';
    const CONFIG_CACHE_ID   = 'pum_config';

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
    * cache provider
    *
    * @var string
    */
    private $cache;

    /**
    * cache id
    *
    * @var string
    */
    private $cache_id;

    public function __construct(Connection $connection, $cacheKey = null)
    {
        $this->connection = $connection;
        $this->tableName  = self::CONFIG_TABLE_NAME;
        $this->cache_id   = self::CONFIG_CACHE_ID;
        $this->setCache($cacheKey);
    }

    /**
    * params string cacheKey
    */
    public function setCache($cacheKey)
    {
        if (extension_loaded('apc')) {
            $this->cache = new Cache\ApcCache();
            $this->cache->setNamespace(md5($cacheKey));
        } else if (extension_loaded('xcache')) {
            $this->cache = new Cache\XcacheCache();
        } else if (extension_loaded('memcache')) {
            $memcache = new \Memcache();
            $memcache->connect('127.0.0.1');
            $this->cache = new Cache\MemcacheCache();
            $this->cache->setMemcache($memcache);
        } else if (extension_loaded('redis')) {
            $redis = new \Redis();
            $redis->connect('127.0.0.1');
            $this->cache = new Cache\RedisCache();
            $this->cache->setRedis($redis);
        } else if (null !== $cacheKey) {
            $this->cache = new Cache\PhpFileCache($cacheKey);
        } else {
            $this->cache = new Cache\ArrayCache();
        }

        $this->cache->setNamespace(md5(self::CONFIG_NAMESPACE.$cacheKey));

        return $this;
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
        if (null !== $this->cache) {
            $this->cache->delete($this->cache_id);
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
        $this->runSQL('DELETE FROM `'.$this->tableName.'`');

        foreach ($this->values as $key => $value) {
            $this->runSQL('INSERT INTO '.$this->tableName.' (`key`, `value`) VALUES ('.$this->connection->quote($key).','.$this->connection->quote(json_encode($value)).');');
        }

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
        $values = $this->cache->fetch($this->cache_id);

        if(!$values) {
            $this->cache->save($this->cache_id, $values = $this->rawRestore(), $lifeTime = 0);
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
        $stmt = $this->runSql('SELECT `key`, `value` FROM `'.$this->tableName.'`');

        $values = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $values[$row['key']] = json_decode($row['value']);
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
            $extra = $this->connection->getDriver() instanceof SqliteDriver ? ';' : ' DEFAULT CHARSET = utf8 COLLATE = utf8_unicode_ci;';
            $this->connection->executeQuery(sprintf('CREATE TABLE %s (`key` VARCHAR(64), `value` TEXT, PRIMARY KEY (`key`))'.$extra, $this->tableName));
        }

        return $this->connection->executeQuery($query, $parameters);
    }
}
