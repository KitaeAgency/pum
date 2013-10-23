<?php

namespace Pum\Core\Extension\View\Twig;

use Pum\Core\Extension\View\View;

class PumViewExtension extends \Twig_Extension
{
    protected $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function getName()
    {
        return 'pum_view';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('pum_view_field', function ($object, $field, $viewName = 'default', array $vars = array()) {
                return $this->view->renderPumField($object, $field, $viewName, $vars);
            }, array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('pum_view_object', function ($object, $viewName = 'default', array $vars = array()) {
                return $this->view->renderPumObject($object, $viewName, $vars);
            }, array('is_safe' => array('html'))),
        );
    }
}
