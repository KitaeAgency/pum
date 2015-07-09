<?php

namespace Pum\Bundle\TypeExtraBundle\Model;

use Pum\Bundle\TypeExtraBundle\Media\StorageInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Pum\Bundle\TypeExtraBundle\Model\MediaMetadata;

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
     * A file pending for storage, or modification of an existing image.
     *
     * @var SplFileInfo
     */
    protected $file;

    /**
     * Mediametadata (contains all meta informations of a file)
     * @var Pum\Bundle\TypeExtraBundle\Model\MediaMetadata
     */
    protected $mediaMetadata;

    /**
     * @param string                                              $id
     * @param string                                              $name
     * @param Symfony\Component\HttpFoundation\File\UploadedFile  $file
     * @param Pum\Bundle\TypeExtraBundle\Model\MediaMetadata      $mediaMetadata
     */
    public function __construct($id = null, $name = null, $file = null, MediaMetadata $mediaMetadata = null)
    {
        $this->id            = $id;
        $this->name          = $name;
        $this->file          = $file;
        $this->mediaMetadata = $mediaMetadata;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Media
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;

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
    public function getName($extension = true)
    {
        if (!$extension && false !== $pos = strrpos($this->name, '.')) {
            return substr($this->name, 0, $pos);
        }

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
        return $this->mediaMetadata->getMime();
    }

    /**
     * @return Media
     */
    public function setMime($mime)
    {
        $this->mediaMetadata->setMine($mime);

        return $this;
    }

    /**
     * @return string
     */
    public function getSize()
    {
        return $this->mediaMetadata->getSize();
    }

    /**
     * @return Media
     */
    public function setSize($size)
    {
        $this->mediaMetadata->setSize($size);

        return $this;
    }

    /**
     * @return string
     */
    public function getHeight()
    {
        return $this->mediaMetadata->getHeight();
    }

    /**
     * @param string $height
     *
     * @return Media
     */
    public function setHeight($height)
    {
        $this->mediaMetadata->setHeight($height);

        return $this;
    }

    /**
     * @return string
     */
    public function getWidth()
    {
        return $this->mediaMetadata->getWidth();
    }

    /**
     * @param string $width
     *
     * @return Media
     */
    public function setWidth($width)
    {
        $this->mediaMetadata->setWidth($width);

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
        if (null === $type = $this->mediaMetadata->getMime()) {
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

    /**
     * @param  Pum\Bundle\TypeExtraBundle\Model\MediaMetadata  $mediaMetadata
     * @return Media
     */
    public function setMediaMetadata(MediaMetadata $mediaMetadata)
    {
        $this->mediaMetadata = $mediaMetadata;

        return $this;
    }

    /**
     * @return MediaMetadata
     */
    public function getMediaMetadata()
    {
        return $this->mediaMetadata;
    }
}
