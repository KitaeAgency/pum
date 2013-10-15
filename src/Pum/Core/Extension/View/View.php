<?php

namespace Pum\Core\Extension\View;

use Pum\Core\ObjectFactory;

class View
{
    const DEFAULT_VIEW = 'default';
    const PATH_PREFIX  = 'pum://';
    const FIELD_PATH   = 'field/';

    /**
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var array
     */
    protected $resources;

    /**
     * @var array
     */
    protected $cache;

    public function __construct(ObjectFactory $objectFactory, \Twig_Environment $twig, array $resources = array())
    {
        $this->objectFactory = $objectFactory;
        $this->twig          = $twig;
        $this->resources     = $resources;
        $this->cache         = array();
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
            self::PATH_PREFIX.self::FIELD_PATH.$type.'/'.$block.'.html.twig',
            self::PATH_PREFIX.self::FIELD_PATH.$type.'/'.$blockDefault.'.html.twig'
        ), $this->resources);

        $block        = 'field_type_'.$type.'_'.$block;
        $blockDefault = 'field_type_'.$type.'_'.$blockDefault;

        if (isset($this->cache[$block])) {
            return $this->twig->loadTemplate($this->cache[$block]['resource'])->renderBlock($this->cache[$block]['block'], $vars);
        }

        foreach ($resources as $resource) {
            try {
                $tpl = $this->twig->loadTemplate($resource);
            } catch (\Twig_Error_Loader $e) {
                continue;
            }

            if ($tpl->hasBlock($block)) {
                $this->cache[$block] = array(
                    'resource' => $resource,
                    'block'    => $block
                );

                return $tpl->renderBlock($block, $vars);
            } else if ($tpl->hasBlock($blockDefault)) {
                $this->cache[$block] = array(
                    'resource' => $resource,
                    'block'    => $blockDefault
                );

                return $tpl->renderBlock($blockDefault, $vars);
            }
        }

        throw new \RuntimeException(sprintf('No block "%s" renderable in resources: %s', $block, implode(', ', $resources)));
    }
}
