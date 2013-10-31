<?php

namespace Pum\Bundle\TypeExtraBundle\Media;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Pum\Bundle\TypeExtraBundle\Exception\MediaNotFoundException;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;

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
    public function store(\SplFileInfo $file)
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
    private function generateFileName(\SplFileInfo $file)
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
    public function getWebPath($id, $width = 0, $height = 0)
    {
        $folder = '';
        if ($width != 0 || $height != 0) {
            $folder = (string)$width . '_' . (string)$height . '/';

            if (!$this->exists($this->getUploadFolder().$folder.$id)) {
                $this->resize($this->getUploadFolder(), $this->getUploadFolder().$folder, $id, $width, $height);
            }
        }

        return $this->path.$folder.$id;
    }

    private function resize($src, $dest, $id, $width, $height)
    {
        if ($this->exists($src.$id)) {
            if (!is_dir($dest)) {
                if (false === @mkdir($dest, 0777, true)) {
                    throw new FileException(sprintf('Unable to create the "%s" directory', $dest));
                }
            } elseif (!is_writable($dest)) {
                throw new FileException(sprintf('Unable to write in the "%s" directory', $dest));
            }

            $imagine = new Imagine();
            $image = $imagine->open($src.$id);
            if ($width && $height) {
                $image->resize(new Box($width, $height));
            } elseif ($height == 0) {
                $image->resize($image->getSize()->widen($width));
            } else {
                $image->resize($image->getSize()->heighten($height));
            }
            $image->save($dest.$id);
        }
    }
}
