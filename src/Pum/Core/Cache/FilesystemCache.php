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
    public function hasClass($class)
    {
        return file_exists($this->cacheDir.'/'.str_replace('\\', '/', $class));
    }

    /**
     * @inherit doc
     */
    public function loadClass($class)
    {
        if (!$this->hasClass($class)) {
            throw new ClassNotFoundException($class);
        }

        require_once $this->cacheDir.'/'.str_replace('\\', '/', $class);
    }

    /**
     * @inherit doc
     */
    public function saveClass($class, $content)
    {
        $file = $this->cacheDir.'/'.str_replace('\\', '/', $class);
        if (!is_dir($dir = dirname($file))) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($file, '<?php '.$content);
        
        require_once $file;
    }

    /**
     * @inherit doc
     */
    public function clear($directory = null)
    {
        $path = $this->cacheDir;
        if ($directory !== null) {
            $path .= '/' . str_replace('\\', DIRECTORY_SEPARATOR, $directory);
        }

        if (is_dir($path)) {
            $filesystem = new Filesystem();
            return $filesystem->remove($path);
        }

        return true;
    }
}
