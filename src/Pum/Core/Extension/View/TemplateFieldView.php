<?php

namespace Pum\Core\Extension\View;

class TemplateFieldView implements ViewFeatureInterface
{
    protected $view;

    /**
     * Constructor.
     *
     * @param 
     */
    public function __construct(ViewStorageInterface $view)
    {
        $this->view  = $view;
    }

    public function getViewTemplates()
    {
        
    }
}