<?php

namespace Pum\Core\Tests\Cache;

abstract class AbstractCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return CacheInterface
     */
    abstract public function getCache();

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
