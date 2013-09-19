<?php

namespace Pum\Extension\View;

use Pum\Core\Object\Object;

class View
{
    /**
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * @var array
     */
    protected $resources;

    public function __construct(\Twig_Environment $twig, array $resources = array())
    {
        $this->twig      = $twig;
        $this->resources = $resources;
    }

    /**
     * Renders field of a given object.
     *
     * @return string result
     */
    public function renderField($object, $field, $block = 'default', array $vars = array())
    {
        $type = $object->_pumGetMetadata()->getTypeName($field);

        $block = 'type_'.$type.'_'.$block;
        $vars  = array_merge(array(
            'value' => $object->get($field),
        ), $vars);

        foreach ($this->resources as $resource) {
            $tpl = $this->twig->loadTemplate($resource);

            if ($tpl->hasBlock($block)) {
                return $tpl->renderBlock($block, $vars);
            }
        }

        throw new \RuntimeException(sprintf('No block "%s" renderable in resources: %s', $block, implode(', ', $this->resources)));
    }
}
