<?php

namespace Pum\Bundle\TypeExtraBundle\Model;

use Pum\Bundle\TypeExtraBundle\Media\StorageInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Media
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $final_name;

    /**
     * @var string
     */
    protected $mime;

    /**
     * @var string
     */
    protected $height;

    /**
     * @var string
     */
    protected $width;

    /**
     * A file pending for storage, or modification of an existing image.
     *
     * @var SplFileInfo
     */
    protected $file;

    /**
     * @param string                                              $id
     * @param string                                              $name
     * @param Symfony\Component\HttpFoundation\File\UploadedFile  $file
     * @param string                                              $mime
     * @param string                                              $width
     * @param string                                              $height
     */
    public function __construct($id = null, $name = null, $file = null, $mime = null, $width = null, $height = null)
    {
        $this->id     = $id;
        $this->name   = $name;
        $this->file   = $file;
        $this->mime   = $mime;
        $this->width  = $width;
        $this->height = $height;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFinalName()
    {
        return $this->final_name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Media
     */
    public function setName($name)
    {
        $this->name       = $name;
        $this->final_name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * @return Media
     */
    public function setMime($mime)
    {
        $this->mime = $mime;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param string $height
     *
     * @return Media
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return string
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param string $width
     *
     * @return Media
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param SplFileInfo $file
     *
     * @return Media
     */
    public function setFile(\SplFileInfo $file)
    {
        // We fake to change name to force Events::ObjectChange TODO :: better way ??
        $this->name = microtime();
        $this->file = $file;

        if (null === $this->final_name || !$this->final_name) {
            if ($file instanceof UploadedFile) {
                $this->final_name = $file->getClientOriginalName();
            } else {
                $this->final_name = $file->getBasename();
            }
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function exists()
    {
        return null !== $this->id;
    }

    /**
     * @return string
     */
    public function getMediaUrl(StorageInterface $storage, $width = 0, $height = 0)
    {
        return $storage->getWebPath($this, $width, $height);
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        if (!$this->exists() || !count($type = explode('.', $this->getId()))) {
            return null;
        }

        return end($type);
    }

    /**
     * @return string
     */
    public function getMediaType()
    {
        return $this->getExtension();
    }

    /**
     * @return boolean
     */
    public function isImage()
    {
        if (null === $type = $this->getMime()) {
            return false;
        }

        return in_array($type, $this->imagesMime());
    }

    /**
     * @return string Generated MD5 token from media ID
     */
    public function getToken()
    {
        if (null === $this->getId()) {
            return null;
        }

        return md5('pum_media_' . $this->getId() . '_media_pum');
    }

    public static function imagesMime()
    {
        return array(
            'image/gif',
            'image/png',
            'image/jpg',
            'image/jpeg'
        );
    }
}
