<?php

namespace Pum\Bundle\TypeExtraBundle\Model;

use Pum\Bundle\TypeExtraBundle\Media\StorageInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Media
{
    protected $name;
    protected $id;
    protected $file;
    protected $storage;

    public function __construct($name, $id, $file = null)
    {
        $this->name = $name;
        $this->id   = $id;
        $this->file = $file;
    }

    public function setStorage($storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Media
     */
    public function upload(UploadedFile $file)
    {
        if ($file instanceof UploadedFile) {
            $media = new self($this->getName(), $this->storage->store($file));
            $this->storage->remove($this->getId());

            return $media;
        }
        
        throw new \InvalidArgumentException(sprintf('Expected a UploadedFile, got a "%s".', is_object($file) ? get_class($file) : gettype($file)));
    }


    /**
     * @return string
     */
    public function getUrl($width = null, $height = null)
    {
        return $this->storage->getWebPath($this->getId(), $width, $height);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name . ", " . $this->id;
    }
}
