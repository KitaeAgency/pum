<?php

namespace Pum\Core\Cache;

use Pum\Core\Exception\ClassNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class FilesystemCache implements CacheInterface
{

    /**
     * @var string
     */
    protected $cacheDir;


    public function __construct($cacheDir)
    {
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $this->cacheDir = $cacheDir;
    }

    /**
     * @inherit doc
     */
    public function getSalt($group = 'default')
    {
        if (file_exists($file = $this->cacheDir.'/'.$group.'/salt')) {
            return require $file;
        }

        if (!is_dir($dir = dirname($file))) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($file, '<?php return "'.md5(uniqid().microtime()).'";');

        return require $file;
    }

    /**
     * @inherit doc
     */
    public function hasClass($class, $group = 'default')
    {
        return file_exists($this->cacheDir.'/'.$group.'/'.$class);
    }

    /**
     * @inherit doc
     */
    public function loadClass($class, $group = 'default')
    {
        if (!$this->hasClass($class, $group)) {
            throw new ClassNotFoundException($class);
        }

        require_once $this->cacheDir.'/'.$group.'/'.$class;
    }

    /**
     * @inherit doc
     */
    public function saveClass($class, $content, $group = 'default')
    {
        $file = $this->cacheDir.'/'.$group.'/'.$class;
        if (!is_dir($dir = dirname($file))) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($file, '<?php '.$content);
        
        require_once $file;
    }

    /**
     * @inherit doc
     */
    public function clear($group = 'default')
    {
        return $this->clearDirectory($this->cacheDir.'/'.$group);
    }

    /**
     * @inherit doc
     */
    public function clearAllGroups()
    {
        return $this->clearDirectory($this->cacheDir);
    }

    public function clearDirectory($dir) 
    {
        $filesystem = new Filesystem();

        return $filesystem->remove($dir);
    }

}