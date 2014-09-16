<?php

namespace Pum\Bundle\TypeExtraBundle\Media;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Pum\Bundle\TypeExtraBundle\Exception\MediaNotFoundException;
use Pum\Bundle\TypeExtraBundle\Model\Media;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

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
        $fileName = $this->generateFileName($file);
        $copy     = $this->getUploadFolder().$fileName;
        $folder   = dirname($copy);

        if (!is_dir($folder)) {
            if (false === @mkdir($folder, 0777, true)) {
                throw new FileException(sprintf('Unable to create the "%s" directory', $folder));
            }
        }
        if (false === @copy($file, $copy)) {
            throw new FileException(sprintf('Unable to write in the "%s" directory', $folder));
        }

        return $fileName;
    }

    /**
     * return the file
     */
    public function getFile($media_id)
    {
        if ($media_id instanceof Media) {
            $media_id = $media_id->getId();
        }

        $file = $this->getUploadFolder().$media_id;

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
     * return webpath
     */
    public function getWebPath(Media $media, $width = 0, $height = 0, $options = array())
    {
        if (false === $media->exists()) {
            return null;
        }

        $folder      = '';
        $id          = $media->getId();
        $forceResize = (isset($options['force_resize'])) ? $options['force_resize'] : false;

        if ($media->isImage()) {
            if ($forceResize || (null !== $media->getWidth() && null !== $media->getHeight() && $media->getWidth() > $width && $media->getHeight() > $height)) {
                if ($width != 0 || $height != 0) {
                    $folder = (string)$width.'_'.(string)$height.'/';

                    if (!$this->exists($this->getUploadFolder().$folder.$id)) {
                        $this->resize($this->getUploadFolder(), $this->getUploadFolder().$folder, $id, $width, $height);
                    }
                }
            }
        }

        return $this->path.$folder.$id;
    }

    /**
     * return webpath
     */
    public function getWebPathFromId($id, $isImage, $width = 0, $height = 0, $options = array())
    {
        $folder = '';

        if ($isImage) {
            if ($width != 0 || $height != 0) {
                $folder = (string)$width.'_'.(string)$height.'/';

                if (!$this->exists($this->getUploadFolder().$folder.$id)) {
                    $this->resize($this->getUploadFolder(), $this->getUploadFolder().$folder, $id, $width, $height);
                }
            }
        }

        return $this->path.$folder.$id;
    }

    /**
     * return string
     */
    public function guessMime(\SplFileInfo $file)
    {
        $guesser = MimeTypeGuesser::getInstance();

        return $guesser->guess($file);
    }

    /**
     * return array
     */
    public function guessImageSize(\SplFileInfo $file)
    {
        if (!in_array($this->guessMime($file), Media::imagesMime())) {
            return array(null, null);
        }

        list($width, $height) = getimagesize($file);

        return array($width, $height);
    }

    private function resize($src, $dest, $id, $width, $height)
    {
        if (!$this->exists($origin = $src.$id)) {
            return;
        }

        if (!is_dir($dest)) {
            if (false === @mkdir($dest, 0777, true)) {
                throw new FileException(sprintf('Unable to create the "%s" directory', $dest));
            }
        } elseif (!is_writable($dest)) {
            throw new FileException(sprintf('Unable to write in the "%s" directory', $dest));
        }

        $imagine = new Imagine();
        $image   = $imagine->open($src.$id);

        if ($width && $height) {
            $image->resize(new Box($width, $height));
        } elseif ($height == 0) {
            $image->resize($image->getSize()->widen($width));
        } else {
            $image->resize($image->getSize()->heighten($height));
        }

        $image->save($dest.$id);
    }

    /**
     * return an unique filename
     */
    private function generateFileName(\SplFileInfo $file)
    {
        $extension = $file->guessExtension();

        if (!$extension || null === $extension) {
            $extension = 'bin';
        } else if ($extension == 'jpeg') {
             $extension = 'jpg';
        }

        $i = 0;
        do {
            $name = $file instanceof UploadedFile ? $file->getClientOriginalName() : $file->getBasename();
            $fileName = md5($name.time().$i).'.'.$extension;
            $i++;
        } while ($this->exists($this->getUploadFolder().$fileName) && $i < 10000);

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
