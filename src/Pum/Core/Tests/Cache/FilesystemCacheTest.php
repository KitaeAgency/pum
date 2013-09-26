<?php

namespace Pum\Core\Tests\Cache;

use Pum\Core\Cache\FilesystemCache;

class FilesystemCacheTest extends AbstractCacheTest
{
    public function getCache()
    {
        $dir = sys_get_temp_dir() . '/pum_';

        return new FilesystemCache($dir);
    }

}
