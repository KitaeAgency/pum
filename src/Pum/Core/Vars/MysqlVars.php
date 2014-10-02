<?php

namespace Pum\Core\Vars;

use Doctrine\Common\Cache;
use Pum\Core\Extension\Util\Namer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;

class MysqlVars implements VarsInterface
{
    const VARS_TABLE_NAME = 'vars_';
    const VARS_NAMESPACE  = 'pum';
    const VARS_CACHE_ID   = 'pum_vars_';

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
    * @var cacheProvider
    */
    private $cache;

    /**
    *
    * @var cacheProvider
    */
    private $cache_id;

    public function __construct(Connection $connection, $projectName, $cacheFolder = null)
    {
        $this->connection  = $connection;
        $this->tableName   = self::VARS_TABLE_NAME.Namer::toLowercase($projectName);
        $this->cache_id    = self::VARS_CACHE_ID.Namer::toLowercase($projectName);
        $this->setCache($cacheFolder);
    }

    /**
    * params string cacheFolder
    */
    public function setCache($cacheFolder)
    {
        if (extension_loaded('apc')) {
            $this->cache = new Cache\ApcCache();
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
        } else if (null !== $cacheFolder) {
            $this->cache = new Cache\PhpFileCache($cacheFolder);
        } else {
            $this->cache = new Cache\ArrayCache();
        }

        $this->cache->setNamespace(self::VARS_NAMESPACE);

        return $this;
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
            'value'       => $this->formatVar($value, $type),
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
        $stmt = $this->runSql('SELECT * FROM `'. $this->tableName .'`');

        $values = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $values[$row['key']] = array(
                'key'         => $row['key'],
                'type'        => $row['type'],
                'value'       => $this->formatVar(json_decode($row['value']), $row['type']),
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

    private function formatVar($var, $type)
    {
        switch ($type) {
            case 'boolean':
                switch (strtolower($var)) {
                    case 'true':
                        return true;

                    case 'false':
                        return false;

                    default:
                        return $var;
                }

            case 'integer':
                return (int)$var;

            case 'float':
                return (float)$var;

            case 'string':
                return (string)$var;

            default:
                return $var;
        }
    }

}
