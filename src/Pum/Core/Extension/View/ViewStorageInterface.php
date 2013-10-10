<?php

namespace Pum\Core\Extension\View;

/**
 * Interface for Template.
 */
interface ViewStorageInterface
{
    /**
    * Return an array of paths .
    *
    * @return array $paths
    */
    public function getAllPaths();

    /**
    * Store a template and return the result.
    *
    * @param TemplateInterface $template
    * @return boolean
    */
    public function storeTemplate(TemplateInterface $template);

    /**
    * Return a template with given path.
    *
    * @param string $path
    * @return TemplateInterface $template
    */
    public function getTemplate($path);

    /**
    * Remove a template and return the result.
    *
    * @param TemplateInterface $template
    * @return boolean
    */
    public function removeTemplate(TemplateInterface $template);

    /**
    * Remove all templates and return the result.
    *
    * @return boolean
    */
    public function removeAllTemplates();

    /**
    * Return if a template exists.
    *
    * @return boolean
    */
    public function hasTemplate($path);
}
