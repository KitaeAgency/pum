<?php

namespace Pum\Core\Extension\View\Twig;

use Pum\Core\Extension\View\View;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PumViewExtension extends \Twig_Extension
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'pum_view';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('pum_view_field', function ($object, $fieldName, $viewName = 'default', array $vars = array()) {
                return $this->container->get('pum.view')->renderPumField($object, $fieldName, $viewName, $vars);
            }, array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('pum_view_object', function ($object, $viewName = 'default', array $vars = array()) {
                return $this->container->get('pum.view')->renderPumObject($object, $viewName, $vars);
            }, array('is_safe' => array('html'))),
        );
    }
}
