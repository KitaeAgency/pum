<?php

namespace Pum\Bundle\TypeExtraBundle\Extension\View\Twig;

use Pum\Bundle\TypeExtraBundle\Extension\View\View;

class PumTypeExtraViewExtension extends \Twig_Extension
{
    protected $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function getName()
    {
        return 'pum_type_extra_view';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('pum_view_media', function ($object, $mediaFieldName, $viewName = 'default', array $vars = array()) {
                return $this->view->renderPumMedia($object, $mediaFieldName, $viewName, $vars);
            }, array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('pum_media_url', function ($id, $isImage, $width = 0, $height = 0) {
                return $this->view->getMediaUrl($id, $isImage, $width, $height);
            }),
        );
    }
}
