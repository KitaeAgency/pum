<?php

namespace Pum\Core\Tests\Vars;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Pum\Core\Vars\MysqlVars;

class MysqlVarsTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSet()
    {
        $file   = $this->getTempFile();
        $vars = $this->getVars($file);

        $this->assertEquals(null, $vars->getValue('non_existing_key'));
        $this->assertEquals('defaultValue', $vars->getValue('non_existing_key', 'defaultValue'));

        $vars->set('existing_key', 'existing_key_value');

        $this->assertEquals('existing_key_value', $vars->getValue('existing_key'));
    }

    public function testRemove()
    {
        $file   = $this->getTempFile();
        $vars = $this->getVars($file);

        $vars->set('existing_key', 'existing_key_value');
        $this->assertEquals('existing_key_value', $vars->getValue('existing_key'));

        $vars->remove('existing_key');
        $this->assertEquals(null, $vars->getValue('existing_key'));
    }

    public function testClearCache()
    {
        $file   = $this->getTempFile();
        $vars = $this->getVars($file);

        $this->assertInstanceOf('Pum\Core\Vars\MysqlVars', $vars->clear());
    }

    public function testFlush()
    {
        $file   = $this->getTempFile();
        $vars = $this->getVars($file);

        $vars->set('existing_key', 'existing_key_value');
        $vars->flush();

        $varsB = $this->getVars($file);
        $this->assertEquals('existing_key_value', $varsB->getValue('existing_key'));
    }

    public function testGetAll()
    {
        $file   = $this->getTempFile();
        $vars = $this->getVars($file);

        $values = array(
            'test' => array('value' => 'test', 'type' => 'string', 'description' => null, 'key' => 'test'),
            'test2' => array('value' => 'test', 'type' => 'string', 'description' => null, 'key' => 'test2'),
            'test3' => array('value' => 'test', 'type' => 'string', 'description' => null, 'key' => 'test3'),
        );

        foreach ($values as $key => $value) {
            $vars->set($key, $value['value']);
        }

        $this->assertEquals($values, $vars->all());
    }

    protected function getVars($file)
    {
        $conn = new Connection(array('path' => $file), new SqliteDriver());

        return new MysqlVars($conn, md5($file));
    }

    protected function getTempFile()
    {
        $file = tempnam(sys_get_temp_dir(), 'gitonomy_');

        register_shutdown_function(function () use ($file) {
            // Skip windows message error on unwrittable file
            @unlink($file);
        });

        return $file;
    }
}
