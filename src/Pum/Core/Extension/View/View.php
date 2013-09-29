<?php

namespace Pum\Core\Extension\View;

use Pum\Core\ObjectFactory;
use Pum\Core\Object\Object;

class View
{
    /**
     * @var Twig_Environment
     */
    protected $twig;

    protected $objectFactory;

    /**
     * @var array
     */
    protected $resources;

    public function __construct(ObjectFactory $objectFactory, \Twig_Environment $twig, array $resources = array())
    {
        $this->objectFactory = $objectFactory;
        $this->twig      = $twig;
        $this->resources = $resources;
    }

    /**
     * Renders field of a given object.
     *
     * @return string result
     */
    public function renderField($object, $field, $block = null, array $vars = array())
    {
        $blockDefault = 'default';
        if (null === $block) {
            $block = $blockDefault;
        }

        list($project, $objectDefinition) = $this->objectFactory->getProjectAndObjectFromClass(get_class($object));

        $field  = $objectDefinition->getField($field);
        $getter = 'get'.ucfirst($field->getCamelCaseName());
        $type = $field->getType();

        $block = 'type_'.$type.'_'.$block;
        $blockDefault = 'type_'.$type.'_'.$blockDefault;

        $vars  = array_merge(array(
            'identifier' => $field->getLowercaseName(),
            'value' => $object->$getter(),
        ), $vars);

        foreach ($this->resources as $resource) {
            $tpl = $this->twig->loadTemplate($resource);

            if ($tpl->hasBlock($block)) {
                return $tpl->renderBlock($block, $vars);
            } else if ($tpl->hasBlock($blockDefault)) {
                return $tpl->renderBlock($blockDefault, $vars);
            }
        }

        throw new \RuntimeException(sprintf('No block "%s" renderable in resources: %s', $block, implode(', ', $this->resources)));
    }
}
