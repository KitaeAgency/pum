<?php

namespace Pum\Core\Cache;

use Pum\Core\Exception\ClassNotFoundException;

class StaticCache implements CacheInterface
{
    /**
     * @var array
     */
    protected $salt = array();

    /**
     * {@inheritdoc}
     */
    public function getSalt($group = 'default')
    {
        if (!isset($this->salt[$group])) {
            $this->salt[$group] = md5(uniqid().microtime());
        }

        return $this->salt[$group];
    }

    /**
     * {@inheritdoc}
     */
    public function hasClass($class, $group = 'default')
    {
        return class_exists($class);
    }

    /**
     * {@inheritdoc}
     */
    public function loadClass($class, $group = 'default')
    {
        throw new ClassNotFoundException($class);
    }

    /**
     * {@inheritdoc}
     */
    public function saveClass($class, $content, $group = 'default')
    {
        if (class_exists($class)) {
            throw new \RuntimeException('Nooo');
        }
        eval($content);
    }

    public function clear($group = 'default')
    {
        unset($this->salt[$group]);
    }

    public function clearAllGroups()
    {
        $this->salt = array();
    }
}
