<?php

namespace Pum\Bundle\TypeExtraBundle\Model;

use Pum\Bundle\TypeExtraBundle\Media\StorageInterface;


class Media
{
    protected $name;
    protected $path;
    protected $file;
    protected $storage;

    public function __construct($name, $path, $file = null)
    {
        $this->name = $name;
        $this->path = $path;
        $this->file = $file;
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
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Media
     */
    public function setPath($path)
    {
        return new self($this->getName(), $path);
    }

    /**
     * @return Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return base64_encode($this->path);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name . ", " . $this->path;
    }
}
