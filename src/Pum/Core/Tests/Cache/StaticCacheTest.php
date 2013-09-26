<?php

namespace Pum\Core\Tests\Cache;

use Pum\Core\Cache\StaticCache;

class StaticCacheTest extends AbstractCacheTest
{
    public function getCache()
    {
        return new StaticCache();
    }
}
