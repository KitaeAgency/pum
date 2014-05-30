<?php

namespace Pum\Bundle\WoodworkBundle\Tests;


use Pum\Bundle\WoodworkBundle\Filesystem\ZipStorage;
use Pum\Core\Definition\Archive\ZipArchive;
use Symfony\Component\Filesystem\Filesystem;

class ZipStorageTest extends \PHPUnit_Framework_TestCase
{
    private $tmpDir;
    private $storage;

    public function setUp()
    {
        $this->tmpDir = sys_get_temp_dir().'/zstest_'.md5(mt_rand()).'/app';
        register_shutdown_function(function () {
            $fs = new Filesystem();
            $fs->remove($this->tmpDir);
        });
        $this->storage = new ZipStorage($this->tmpDir.'/cache', $this->tmpDir);
    }


    public function testConstructor()
    {
        $this->assertFileExists($this->tmpDir.'/cache/zipArchive/');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetNonExistentZip()
    {
        $this->storage->getZip('test_not_exist');
    }

    public function testGetZip()
    {
        file_put_contents($this->tmpDir.'/cache/zipArchive/test.zip', '');
        $this->assertInstanceOf('Pum\Core\Definition\Archive\ZipArchive', $this->storage->getZip('test'));
    }
}
