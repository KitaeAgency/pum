<?php

namespace Pum\Bundle\TypeExtraBundle\Media;

use Pum\Bundle\TypeExtraBundle\Exception\MediaNotFoundException;

/**
 * Implementation of driver using a PHP array (no persistence).
 */
class FilesystemStorage implements StorageInterface
{
    protected $directory;
    protected $path;

    public function __construct($directory, $path)
    {
        $this->directory = $directory;
        $this->path      = $path;
    }

    /**
     * store a file
     */
    public function store($file, $oldFilePath = null)
    {
        if (null === $file) {
            return;
        }

        $fileName = $this->generateFileName($file);
        if (!$this->exists($this->getUploadFolder().$fileName)) {
            $file->move($this->getUploadFolder(), $fileName);

            if ($oldFilePath !== null) {
                $this->remove($oldFilePath);
            }

            return $this->path.$fileName;
        }

        return;
    }

    /**
     * return the file url
     */
    public function getFile($path)
    {
        $file = $this->directory.$path;

        if ($this->exists($file)) {
            header('Pragma: public');
            header('Content-type: image/jpg');
            header('Cache-Control: public');
            readfile($file);
            
            exit;
        }

        throw new MediaNotFoundException($file);
    }

    /**
     * remove a file
     */
    public function remove($path)
    {
        if ($path && $this->exists($this->directory.$path)) {
            return unlink($this->directory.$path);
        }
        
        return false;
    }

    /**
     * return file existence
     */
    public function exists($file)
    {
        return file_exists($file);
    }

    /**
     * return an unique filename
     */
    private function generateFileName($file)
    {
        $extension = $file->guessExtension();
        if (!$extension) {
            $extension = 'bin';
        }

        $i = 0;
        do {
            $fileName = md5($file->getClientOriginalName().time().$i).'.'.$extension;
            $i++;
        } while ($this->exists($this->getUploadFolder().$fileName) && $i < 100);

        return $fileName;
    }

    /**
     * return upload directory
     */
    private function getUploadFolder()
    {
        return $this->directory.$this->path;
    }
}
