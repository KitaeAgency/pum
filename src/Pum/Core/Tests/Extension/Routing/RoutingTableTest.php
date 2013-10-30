<?php

namespace Pum\Core\Tests\Extension\View;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Pum\Core\Extension\Routing\RoutingTable;

class RoutingTableTest extends \PHPUnit_Framework_TestCase
{
    public function testSet()
    {
        $table = $this->getRoutingTable();

        $table->set('foobar', 'foo');
        $this->assertEquals('foo', $table->match('foobar'));
    }

    public function testAdd()
    {
        $table = $this->getRoutingTable();

        $table->set('foobar', 'foo');
        $this->assertEquals('foobar-2', $table->add('foobar', 'bar'));
        $this->assertEquals('foobar-3', $table->add('foobar', 'baz'));

        $this->assertEquals('foo', $table->match('foobar'));
        $this->assertEquals('bar', $table->match('foobar-2'));
        $this->assertEquals('baz', $table->match('foobar-3'));
    }

    public function testDeleteByValue()
    {
        $table = $this->getRoutingTable();

        $table->set('foo', 'foo');
        $table->set('bar', 'foo');
        $table->set('baz', 'bar');
        $table->deleteByValue('foo');

        $this->assertNull($table->match('foo'));
        $this->assertEquals('bar', $table->match('baz'));
    }

    public function testPurge()
    {
        $table = $this->getRoutingTable();

        $table->set('foo', 'foo');
        $table->set('bar', 'foo');
        $table->set('baz', 'bar');
        $table->purge();

        $this->assertNull($table->match('foo'));
        $this->assertNull($table->match('baz'));
    }

    public function testUnknownRoute()
    {
        $table = $this->getRoutingTable();

        $result = $table->match('foobar');
        $this->assertNull($result);
    }

    protected function getRoutingTable($name = 'default')
    {
        $tempFile = $this->getTempFile();
        $conn = new Connection(array('path' => $tempFile), new SqliteDriver());

        return new RoutingTable($conn, $name);
    }

    protected function getTempFile()
    {
        $file = tempnam(sys_get_temp_dir(), 'pum_');

        register_shutdown_function(function () use ($file) {
            // Skip windows message error on unwrittable file
            @unlink($file);
        });

        return $file;
    }
}
