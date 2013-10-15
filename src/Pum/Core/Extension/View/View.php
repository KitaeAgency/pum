<?php

namespace Pum\Core\Extension\View;

use Pum\Core\ObjectFactory;

class View
{
    const DEFAULT_VIEW = 'default';
    const PATH_PREFIX  = 'pum://';

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
        $this->twig          = $twig;
        $this->resources     = $resources;
    }

    /**
     * Renders field of a given object.
     *
     * @return string result
     */
    public function renderPumField($object, $fieldName, $block = null, array $vars = array())
    {
        $blockDefault = self::DEFAULT_VIEW;
        if (null === $block) {
            $block = $blockDefault;
        }

        list($project, $objectDefinition) = $this->objectFactory->getProjectAndObjectFromClass(get_class($object));

        $field  = $objectDefinition->getField($fieldName);
        $getter = 'get'.ucfirst($field->getCamelCaseName());
        $type   = $field->getType();

        $vars  = array_merge(array(
            'identifier' => $field->getLowercaseName(),
            'value'      => $object->$getter(),
        ), $vars);

        $resources = array_merge(array(
            self::PATH_PREFIX.'field/'.$type.'/'.$block.'.html.twig',
            self::PATH_PREFIX.'field/'.$type.'/'.$blockDefault.'.html.twig'
        ), $this->resources);

        $block        = 'field_type_'.$type.'_'.$block;
        $blockDefault = 'field_type_'.$type.'_'.$blockDefault;

        foreach ($resources as $resource) {
            try {
                $tpl = $this->twig->loadTemplate($resource);
            } catch (\Twig_Error_Loader $e) {
                continue;
            }

            if ($tpl->hasBlock($block)) {
                return $tpl->renderBlock($block, $vars);
            } else if ($tpl->hasBlock($blockDefault)) {
                return $tpl->renderBlock($blockDefault, $vars);
            }
        }

        throw new \RuntimeException(sprintf('No block "%s" renderable in resources: %s', $block, implode(', ', $resources)));
    }
}
