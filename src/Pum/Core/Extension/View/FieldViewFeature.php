<?php

namespace Pum\Core\Extension\View;

use Pum\Core\Extension\View\Template\Template;
use Pum\Core\Extension\View\Storage\ViewStorageInterface;

class FieldViewFeature extends AbstractViewFeature
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
        return $this->view->getAllPaths($type = Template::TYPE_FIELD);
    }

    public function importFromFilessystem()
    {
        
    }
}