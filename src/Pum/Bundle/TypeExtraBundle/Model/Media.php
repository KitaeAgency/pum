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
     * A file pending for storage, or modification of an existing image.
     *
     * @var SplFileInfo
     */
    protected $file;

    /**
     * @param string $id
     * @param string $name
     */
    public function __construct($id = null, $name = null, $file = null)
    {
        $this->id      = $id;
        $this->name    = $name;
        $this->file    = $file;
    }

    public function exists()
    {
        return null !== $this->id;
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
    public function getMediaUrl(StorageInterface $storage, $width = 0, $height = 0)
    {
        return $this->exists() ? $storage->getWebPath($this->getId(), $this->getIsImage(), $width, $height) : null;
    }

    /**
     * @return string
     */
    public function getMediaType()
    {
        if (!$this->exists() || !count($type = explode('.', $this->getId()))) {
            return null;
        }

        return end($type);
    }

    /**
     * @return string
     */
    public function getIsImage()
    {
        if (!$this->exists() || !count($type = explode('.', $this->getId()))) {
            return false;
        }

        $type = end($type);

        return in_array(strtolower($type), array('jpeg', 'jpg', 'png', 'gif'));
    }
}
