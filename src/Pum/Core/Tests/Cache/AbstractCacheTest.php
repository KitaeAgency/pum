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
    }
}
