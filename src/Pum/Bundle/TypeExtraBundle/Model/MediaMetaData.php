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
     * @var string
     */
    private $height;

    /**
     * @param   string  $mime
     * @param   string  $width
     * @param   string  $height
     */
    public function __construct($mime, $width, $height)
    {
        $this->mime = $mime;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @param   string  $mime
     * @return  MediaMetadata
     */
    public function setMime($mime)
    {
        $this->mime = $mime;

        return $this;
    }

    /**
     * @return  string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * @param   string  $width
     * @return  MediaMetadata
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return  string
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param  string  $height
     * @return  MediaMetadata
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return  string
     */
    public function getHeight()
    {
        return $this->height;
    }
}
