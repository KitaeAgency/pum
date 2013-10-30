<?php

namespace Pum\Core\Extension\View\Template;

class Template implements TemplateInterface
{
    const TYPE_DEFAULT = 1;
    const TYPE_BEAM    = 2;
    const TYPE_OBJECT  = 3;
    const TYPE_FIELD   = 4;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var boolean
     */
    protected $is_editable;

    /**
     * @var boolean
     */
    protected $is_root;

    /**
     * @var integer
     */
    protected $time;

    public function __construct($path = null, $source = null, $time = null)
    {
        $this->setPath($path);
        $this->setSource($source);
        $this->setTime($time);
        $this->setIsEditable();
        $this->setIsRoot();
    }

    /**
     * @construct
     */
    public static function create($path = null, $source = null, $time = null)
    {
        return new self($path, $source, $time);
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
     * @return string
     */
    public function getSource()
    {
        return $this->source;
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
     * @return boolean
     */
    public function isEditable()
    {
        return $this->is_editable;
    }

    /**
     * @return Template
     */
    public function setIsEditable($is_editable = null)
    {
        if (is_null($is_editable)) {
            if (strpos($this->source, '{# not editable #}') === false) {
                $this->is_editable = true;
            } else {
                $this->is_editable = false;
            }
        } else {
            $this->is_editable = (boolean)$is_editable;
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function isRoot()
    {
        return $this->is_root;
    }

    /**
     * @return Template
     */
    public function setIsRoot($is_root = null)
    {
        if (is_null($is_root)) {
            if (strpos($this->source, '{# root #}') === false) {
                $this->is_root = false;
            } else {
                $this->is_root = true;
            }
        } else {
            $this->is_root = (boolean)$is_root;
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
    public function getType()
    {
        return $this->guessType();
    }

    /**
     * Look at path conventions https://github.com/les-argonautes/pum/blob/master/doc/draft/view.rst 
     */
    protected function guessType()
    {
        $path = $this->path;

        if (preg_match('/^(beam\/)([a-zA-Z0-9-_]+\/)+([a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+\.twig)$/', $path, $results)) {
            return self::TYPE_BEAM;
        }

        if (preg_match('/^(project\/)([a-zA-Z0-9-_]+\/)(beam\/)([a-zA-Z0-9-_]+\/)+([a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+\.twig)$/', $path, $results)) {
            return self::TYPE_BEAM;
        }

        if (preg_match('/^(object\/)([a-zA-Z0-9-_]+\/){2,}([a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+\.twig)$/', $path, $results)) {
            return self::TYPE_OBJECT;
        }

        if (preg_match('/^(project\/)([a-zA-Z0-9-_]+\/)(object\/)([a-zA-Z0-9-_]+\/){2,}([a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+\.twig)$/', $path, $results)) {
            return self::TYPE_OBJECT;
        }

        if (preg_match('/^(field\/)([a-zA-Z0-9-_]+\/)+([a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+\.twig)$/', $path, $results)) {
            return self::TYPE_FIELD;
        }

        if (preg_match('/^(project\/)([a-zA-Z0-9-_]+\/)(field\/)([a-zA-Z0-9-_]+\/)+([a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+\.twig)$/', $path, $results)) {
            return self::TYPE_FIELD;
        }

        return self::TYPE_DEFAULT;
    }
}
