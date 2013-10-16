<?php

namespace Pum\Core\Extension\View;

abstract class AbstractViewFeature implements ViewFeatureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getViewTemplates()
    {
        return $this->view->getAllPaths();
    }
}