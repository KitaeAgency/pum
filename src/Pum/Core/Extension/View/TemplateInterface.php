<?php

namespace Pum\Core\Extension\View;

/**
 * Interface for Template.
 */
interface TemplateInterface
{
    /**
    * Return path of a template as string.
    *
    * @return string
    */
    public function getPath();

    /**
    * Return the source of a template.
    *
    * @return string
    */
    public function getSource();

    /**
    * Return if a template is editable.
    *
    * @return boolean
    */
    public function isEditable();
}
