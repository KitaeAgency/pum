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
    public function store($file)
    {
        if (null === $file) {
            return;
        }

        $fileName = $this->generateFileName($file);
        if (!$this->exists($this->getUploadFolder().$fileName)) {
            $file->move($this->getUploadFolder(), $fileName);

            return $fileName;
        }

        return;
    }

    /**
     * return the file
     */
    public function getFile($id)
    {
        $file = $this->getUploadFolder().$id;

        if ($this->exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);

            exit;
        }

        throw new MediaNotFoundException($file);
    }

    /**
     * remove a file
     */
    public function remove($id)
    {
        if ($id && $this->exists($this->getUploadFolder().$id)) {
            return unlink($this->getUploadFolder().$id);
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

    /**
     * return webpath
     */
    public function getWebPath($id, $width = null, $height = null)
    {
        $folder = '';
        if ($width !== null && $height !== null){
            $folder = (string)$width . '_' . (string)$height . '/';

            if (!$this->exists($this->path.$folder.$id)) {
                //Create image
            }
        }

        return $this->path.$folder.$id;
    }
}
