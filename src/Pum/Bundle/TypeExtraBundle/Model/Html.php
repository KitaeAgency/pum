<?php

namespace Pum\Bundle\TypeExtraBundle\Model;


class Html
{
    protected $content;

    public function __construct($content = null)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return Html
     */
    public function setContent($content)
    {
        $this->content = $content;
        
        return $this;
    }

    public function __toString()
    {
        return (string) $this->content;
    }
}
