<?php

namespace Pum\Core\View\Twig;

use Pum\Core\Object\Object;
use Pum\Core\View\View;

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
            new \Twig_SimpleFunction('pum_view_field', function (Object $object, $field, $blockName = 'default', array $vars = array()) {
                return $this->view->renderField($object, $field, $blockName, $vars);
            }, array('is_safe' => array('html'))),
        );
    }
}
