<?php

namespace Pum\Core\Extension\View;

use Pum\Core\ObjectFactory;

class View
{
    const DEFAULT_VIEW = 'default';
    const PATH_PREFIX  = 'pum://';
    const PROJECT_PATH = 'project/';
    const OBJECT_PATH  = 'object/';
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

    /**
     * @param Twig_Environment $twig twig instance with "pum://" loader already injected
     */
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
        if (null === $view) {
            $view = self::DEFAULT_VIEW;
        }

        list($project, $objectDefinition) = $this->objectFactory->getProjectAndObjectFromClass(get_class($object));

        $field  = $objectDefinition->getField($fieldName);
        $getter = 'get'.ucfirst($field->getCamelCaseName());
        $type   = $field->getType();
        if ('relation' == $type) {
            $typeOptions = $field->getTypeOptions();
            $linkParams = array(
                'project' => $project->getName(),
                'beam'    => $typeOptions['target_beam'],
                'object'  => $typeOptions['target']
            );
        } else {
            $linkParams = null;
        }

        /* Vars for templates */
        $vars  = array_merge(array(
            'identifier' => $field->getLowercaseName(),
            'value'      => $object->$getter(),
            'linkparams' => $linkParams
        ), $vars);

        /* Templates Priority */
        $templates = array_unique(array(
            self::PATH_PREFIX.self::PROJECT_PATH.$project->getLowercaseName().'/'.self::FIELD_PATH.$type.'/'.$view.'.html.twig',
            self::PATH_PREFIX.self::PROJECT_PATH.$project->getLowercaseName().'/'.self::FIELD_PATH.$type.'/'.self::DEFAULT_VIEW.'.html.twig',
            self::PATH_PREFIX.self::FIELD_PATH.$type.'/'.$view.'.html.twig',
            self::PATH_PREFIX.self::FIELD_PATH.$type.'/'.self::DEFAULT_VIEW.'.html.twig'
        ));

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

        throw new \RuntimeException(sprintf('No field template "%s" found in resources: %s', $type.'/'.$view, implode(', ', $templates)));
    }

    /**
     * Renders an object of a given beam.
     *
     * @return string result
     */
    public function renderPumObject($object, $view = null, array $vars = array())
    {
        if (null === $view) {
            $view = self::DEFAULT_VIEW;
        }

        list($project, $objectDefinition) = $this->objectFactory->getProjectAndObjectFromClass(get_class($object));

        $beamName   = $objectDefinition->getBeam()->getName();
        $objectName = $objectDefinition->getName();

        /* Vars for templates */
        $vars  = array_merge(array(
            'identifier'  => $objectName,
            'object'      => $object,
        ), $vars);

        /* Templates Priority */
        $templates = array_unique(array(
            self::PATH_PREFIX.self::PROJECT_PATH.$project->getLowercaseName().'/'.self::OBJECT_PATH.$beamName.'/'.$objectName.'/'.$view.'.html.twig',
            self::PATH_PREFIX.self::PROJECT_PATH.$project->getLowercaseName().'/'.self::OBJECT_PATH.$beamName.'/'.$objectName.'/'.self::DEFAULT_VIEW.'.html.twig',
            self::PATH_PREFIX.self::OBJECT_PATH.$beamName.'/'.$objectName.'/'.$view.'.html.twig',
            self::PATH_PREFIX.self::OBJECT_PATH.$beamName.'/'.$objectName.'/'.self::DEFAULT_VIEW.'.html.twig',
            self::PATH_PREFIX.self::OBJECT_PATH.$objectName.'/'.$view.'.html.twig',
            self::PATH_PREFIX.self::OBJECT_PATH.$objectName.'/'.self::DEFAULT_VIEW.'.html.twig'
        ));

        /* Template cache */
        if (isset($this->cache['object_'.$beamName.'_'.$objectName.'_'.$view])) {
            return $this->twig->loadTemplate($this->cache['object_'.$beamName.'_'.$objectName.'_'.$view])->render($vars);
        }

        /* Search templates by priorty */
        foreach ($templates as $template) {
            try {
                $tpl = $this->twig->loadTemplate($template);
                $this->cache['object_'.$beamName.'_'.$objectName.'_'.$view] = $template;
            } catch (\Twig_Error_Loader $e) {
                continue;
            }

            return $tpl->render($vars);
        }

        throw new \RuntimeException(sprintf('No object template "%s" found in resources: %s', $objectName.'/'.$view, "\n -".implode("\n- ", $templates)));
    }
}
