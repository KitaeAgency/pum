<?php

namespace Pum\Core\Extension\View;

use Pum\Core\ObjectFactory;

class View
{
    const DEFAULT_VIEW = 'default';
    const PATH_PREFIX  = 'pum://';
    const PROJECT_PATH = 'project/';
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
    protected $cache;

    public function __construct(ObjectFactory $objectFactory, \Twig_Environment $twig)
    {
        $this->objectFactory = $objectFactory;
        $this->twig          = $twig;
        $this->cache         = array();
    }

    /**
     * Renders field of a given object.
     *
     * @return string result
     */
    public function renderPumField($object, $fieldName, $view = null, array $vars = array())
    {
        $viewDefault = self::DEFAULT_VIEW;
        if (null === $view) {
            $view = $viewDefault;
        }

        list($project, $objectDefinition) = $this->objectFactory->getProjectAndObjectFromClass(get_class($object));

        $field  = $objectDefinition->getField($fieldName);
        $getter = 'get'.ucfirst($field->getCamelCaseName());
        $type   = $field->getType();

        /* Vars for templates */
        $vars  = array_merge(array(
            'identifier' => $field->getLowercaseName(),
            'value'      => $object->$getter(),
        ), $vars);

        /* Templates Priority */
        $templates = array(
            self::PATH_PREFIX.self::PROJECT_PATH.$project->getLowercaseName().self::FIELD_PATH.$type.'/'.$view.'.html.twig',
            self::PATH_PREFIX.self::PROJECT_PATH.$project->getLowercaseName().self::FIELD_PATH.$type.'/'.$viewDefault.'.html.twig',
            self::PATH_PREFIX.self::FIELD_PATH.$type.'/'.$view.'.html.twig',
            self::PATH_PREFIX.self::FIELD_PATH.$type.'/'.$viewDefault.'.html.twig'
        );

        /* Template cache */
        if (isset($this->cache['field_'.$type.'_'.$view])) {
            return $this->twig->loadTemplate($this->cache['field_'.$type.'_'.$view])->render($vars);
        }

        /* Search templates by priorty */
        foreach ($templates as $template) {
            try {
                $tpl = $this->twig->loadTemplate($template);
                $this->cache['field_'.$type.'_'.$view] = $template;
            } catch (\Twig_Error_Loader $e) {
                continue;
            }

            return $tpl->render($vars);
        }

        throw new \RuntimeException(sprintf('No block "%s" renderable in resources: %s', $view, implode(', ', $templates)));
    }
}
