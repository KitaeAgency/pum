<?php

namespace Pum\Core\Tests\Cache;

abstract class AbstractCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return CacheInterface
     */
    abstract public function getCache();

    public function testSalt()
    {
        $cache = $this->getCache();

        $this->assertNotNull($salt = $cache->getSalt('foo'));
        $this->assertEquals($salt, $cache->getSalt('foo'));

        $cache->clear('foo');

        $this->assertNotEquals($salt, $cache->getSalt('foo'));
        $salt = $cache->getSalt('foo');

        $cache->clear('bar');
        $this->assertEquals($salt, $cache->getSalt('foo'));

        $cache->clearAllGroups();

        $this->assertNotEquals($salt, $cache->getSalt('foo'));
    }

    public function testClass()
    {
        $cache = $this->getCache();
        $rand = md5(uniqid().microtime());
        $class = 'obj_test_'.$rand;

        $content = 'class '.$class.' {}';
        $cache->saveClass($class, $content, 'test');

        $classTestClassInstance = new $class();
    }

    /**
     * @expectedException \Pum\Core\Exception\ClassNotFoundException
     */
    public function testClassNotFound()
    {
        $cache = $this->getCache();
        $cache->loadClass('obj_classTestClass');
    }

}
