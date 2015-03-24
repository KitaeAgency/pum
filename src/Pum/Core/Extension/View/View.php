<?php

namespace Pum\Core\Extension\View;

use Pum\Core\ObjectFactory;
use Pum\Core\Extension\Util\Namer;
use Pum\Bundle\CoreBundle\Routing\PumSeoGenerator;

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
     * @var PumSeoGenerator
     */
    protected $routingGenerator;

    /**
     * @param Twig_Environment $twig twig instance with "pum://" loader already injected
     */
    public function __construct(ObjectFactory $objectFactory, \Twig_Environment $twig, PumSeoGenerator $routingGenerator)
    {
        $this->objectFactory = $objectFactory;
        $this->twig          = $twig;
        $this->cache         = array();
        $this->routingGenerator = $routingGenerator;
    }

    /**
     * Renders field of a given object.
     *
     * @return string result
     */
    public function renderPumField($object, $fieldName, $view = null, array $vars = array(), $type = null)
    {
        if (null === $view) {
            $view = self::DEFAULT_VIEW;
        }

        list($project, $objectDefinition) = $this->objectFactory->getProjectAndObjectFromClass(get_class($object));

        $field = null;
        if ($objectDefinition->hasField($fieldName)) {
            $field      = $objectDefinition->getField($fieldName);
            $identifier = $field->getLowercaseName();
            $getter     = 'get'.ucfirst($field->getCamelCaseName());
            $type       = $field->getType();
        } else {
            $identifier = Namer::toLowercase($fieldName);
            $getter     = 'get'.ucfirst(Namer::toCamelCase($fieldName));
            if (null === $type) {
                $type   = gettype($object->$getter());
            }
        }

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
            'identifier' => $identifier,
            'value'      => $object->$getter(),
            'linkparams' => $linkParams
        ), $vars);

        if ($field && isset($vars['value']) && $type == 'choice') {
            $translation = implode('.', array($field->getTranslatedName(), $vars['value']));
            $translated = $this->twig->getExtension('translator')->trans(
                $translation,
                array(),
                'pum_schema'
            );

            if ($translation != $translated) {
                $vars['value'] = $translated;
            }
        }

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

    /**
     * Render the default template of the given object
     */
    public function render($object, $vars = array())
    {
        list($project, $objectDefinition) = $this->objectFactory->getProjectAndObjectFromClass(get_class($object));

        $beamName   = $objectDefinition->getBeam()->getName();
        $objectName = $objectDefinition->getName();

        $vars  = array_merge(array(
            'identifier'  => $objectName,
            'object'      => $object,
        ), $vars);

        $template = $this->routingGenerator->getTemplate($object, true);

        $tpl = $this->twig->loadTemplate($template);

        return $tpl->display($vars);
    }
}
