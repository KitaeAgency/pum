<?php

namespace Pum\Core\Extension\View;

class Template implements TemplateInterface
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $is_editable;

    /**
     * @var integer
     */
    protected $time;

    public function __construct($path = null, $source = null, $is_editable = null, $time = null)
    {
        $this->setPath($path);
        $this->setSource($source);
        $this->setIsEditable($is_editable);
        $this->setTime($time);
    }

    /**
     * @construct
     */
    public static function create($path = null, $source = null, $is_editable = null, $time = null)
    {
        return new self($path, $source, $is_editable, $time);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return Template
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return Template
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return boolean
     */
    public function isEditable()
    {
        return $this->is_editable;
    }

    /**
     * @return Template
     */
    public function setIsEditable($is_editable)
    {
        if (is_null($is_editable)) {
            if (strpos($this->source, '{# not editable #}') === false) {
                $this->is_editable = true;
            } else {
                $this->is_editable = false;
            }
        } else {
            $this->is_editable = $is_editable;
        }

        return $this;
    }

    /**
     * @return Template
     */
    public function setTime($time)
    {
        if ($time === null) {
            $this->time = time();
        } else {
            $this->time = $time;
        }

        return $this;
    }

    /**
     * @return integer
     */
    public function getTime()
    {
        return $this->time;
    }

}
