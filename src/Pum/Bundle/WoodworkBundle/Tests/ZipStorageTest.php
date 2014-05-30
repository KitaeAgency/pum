<?php

namespace Pum\Bundle\WoodworkBundle\Tests;


use Pum\Bundle\WoodworkBundle\Filesystem\ZipStorage;
use Pum\Core\Definition\Archive\ZipArchive;

class ZipStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        new ZipStorage(sys_get_temp_dir());
        $this->assertFileExists(sys_get_temp_dir().'/zipArchive/');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetNonExistentZip()
    {
        $zipStorage = new ZipStorage(sys_get_temp_dir());
        $zipStorage->getZip('test_not_exist');
    }

    public function testGetZip()
    {
        file_put_contents(sys_get_temp_dir().'/zipArchive/test.zip', '');
        $zipStorage = new ZipStorage(sys_get_temp_dir());

        $this->assertInstanceOf('Pum\Core\Definition\Archive\ZipArchive', $zipStorage->getZip('test'));
    }

      //TODO this test is commented for now due to schemaInterface dependency on ZipArchive
//    public function testSaveZip()
//    {
//        $zipStorage = new ZipStorage(sys_get_temp_dir());
//        $zipArchive = new ZipArchive();
//        $zipArchive->closeArchive();
//        $zipId = $zipStorage->saveZip($zipArchive);
//
//        $this->assertInternalType('integer', $zipId);
//        $this->assertFileExists(sys_get_temp_dir().'/zipArchive/'.$zipId.'.zip');
//    }
}
