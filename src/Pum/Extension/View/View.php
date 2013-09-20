<?php

namespace Pum\Extension\View;

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
    public function renderField($object, $field, $block = 'default', array $vars = array())
    {
        list($project, $objectDefinition) = $this->objectFactory->getProjectAndObjectFromClass(get_class($object));

        $field  = $objectDefinition->getField($field);
        $getter = 'get'.ucfirst($field->getCamelCaseName());
        $type = $field->getType();

        $block = 'type_'.$type.'_'.$block;
        $vars  = array_merge(array(
            'value' => $object->$getter(),
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
