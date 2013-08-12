<?php

namespace Pum\Bundle\TypeExtraBundle\Model;

class Media
{
    protected $name;
    protected $path;

    public function __construct($name, $path)
    {
        $this->name = $name;
        $this->path = $path;
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
    public function __toString()
    {
        return $this->name . ", " . $this->path;
    }
}
