<?php

namespace Pum\Bundle\TypeExtraBundle\Model;

use Pum\Bundle\TypeExtraBundle\Media\StorageInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Media
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * A file pending for storage, or modification of an existing image.
     *
     * @var SplFileInfo
     */
    protected $file;

    /**
     * @param string $id
     * @param string $name
     */
    public function __construct(StorageInterface $storage, $id = null, $name = null)
    {
        $this->storage = $storage;
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return Media
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
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

    /**
     * @return Media
     */
    public function setFile(\SplFileInfo $file)
    {
        $this->file = $file;
        $this->name = $file->getBasename();

        return $this;
    }

    /**
     * @return Media
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function deleteStorage()
    {

        if (null === $this->id) {
            return;
        }

        $this->storage->remove($this->id);
        $this->id = null;

        return $this;
    }

    public function flushStorage()
    {
        if (null === $this->file) {
            return;
        }

        $this->deleteStorage();

        $this->id   = $this->storage->store($this->file);
        $this->file = null;
    }


    /**
     * @return string
     */
    public function getImageUrl($width = 0, $height = 0)
    {
        return $this->storage->getWebPath($this->getId(), $width, $height);
    }
}
