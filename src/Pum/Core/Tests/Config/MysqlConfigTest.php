<?php

namespace Pum\Core\Tests\Config;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Pum\Core\Config\MysqlConfig;

class MysqlConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSet()
    {
        $file   = $this->getTempFile();
        $config = $this->getConfig($file);

        $this->assertEquals(null, $config->get('non_existing_key'));
        $this->assertEquals('defaultValue', $config->get('non_existing_key', 'defaultValue'));

        $config->set('existing_key', 'existing_key_value');

        $this->assertEquals('existing_key_value', $config->get('existing_key'));
    }

    public function testRemove()
    {
        $file   = $this->getTempFile();
        $config = $this->getConfig($file);

        $config->set('existing_key', 'existing_key_value');
        $this->assertEquals('existing_key_value', $config->get('existing_key'));

        $config->remove('existing_key');
        $this->assertEquals(null, $config->get('existing_key'));
    }

    public function testClearCache()
    {
        $file   = $this->getTempFile();
        $config = $this->getConfig($file);

        $this->assertEquals(true, $config->clear());
    }

    public function testFlush()
    {
        $file   = $this->getTempFile();
        $config = $this->getConfig($file);

        $config->set('existing_key', 'existing_key_value');
        $config->flush();

        $configB = $this->getConfig($file);
        $this->assertEquals('existing_key_value', $configB->get('existing_key'));
    }

    public function testGetAll()
    {
        $file   = $this->getTempFile();
        $config = $this->getConfig($file);

        $values = array(
            'test' => 'test',
            'test2' => 'test2',
            'test3' => 'test3',
        );

        foreach ($values as $key => $value) {
            $config->set($key, $value);
        }

        $this->assertEquals($values, $config->all());
    }

    protected function getConfig($file)
    {
        $conn = new Connection(array('path' => $file), new SqliteDriver());

        return new MysqlConfig($conn, md5($file));
    }

    protected function getTempFile()
    {
        $file = tempnam(sys_get_temp_dir(), 'gitonomy_');

        if (file_exists($file)) {
            unlink($file);
        }

        register_shutdown_function(function () use ($file) {
            // Skip windows message error on unwrittable file
            @unlink($file);
        });

        return $file;
    }
}
