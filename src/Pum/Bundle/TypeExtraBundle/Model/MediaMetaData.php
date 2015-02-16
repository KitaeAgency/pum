<?php

namespace Pum\Bundle\TypeExtraBundle\Model;

class MediaMetadata
{

    /**
     * @var string
     */
    private $mime;

    /**
     * @var string
     */
    private $width;

    /**
     * @var height
     */
    private $height;

    public function __construct($mime, $width, $height)
    {
        $this->mime = $mime;
        $this->width = $width;
        $this->height = $height;
    }

    public function setMime($mime)
    {
        $this->mime = $mime;
    }

    public function getMime()
    {
        return $this->mime;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function getHeight()
    {
        return $this->height;
    }
}
