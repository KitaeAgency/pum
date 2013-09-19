<?php

namespace Pum\Core\Cache;

interface CacheInterface
{
    public function getSalt($group = 'default');
    public function hasClass($class, $group = 'default');
    public function loadClass($class, $group = 'default');
    public function saveClass($class, $content, $group = 'default');
    public function clear($group = 'default');
    public function clearAllGroups();
}
