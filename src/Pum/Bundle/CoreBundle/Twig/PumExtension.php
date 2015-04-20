<?php

namespace Pum\Bundle\CoreBundle\Twig;

use Pum\Bundle\CoreBundle\PumContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PumExtension extends \Twig_Extension
{
    /**
     * @var PumContext
     */
    private $context;

    public function __construct(PumContext $context)
    {
        $this->context = $context;
    }
    
    protected function getTranslator()
    {
        return $this->context->getContainer()->get('translator');
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('pum_projectName', function () {
                return $this->context->getProjectName();
            }),
            new \Twig_SimpleFunction('pum_projects', function () {
                return $this->context->getAllProjects();
            }),
            new \Twig_SimpleFunction('pum_project', function () {
                return $this->context->getProject();
            }),
            new \Twig_SimpleFunction('pum_path', function ($obj, array $params = array(), $routeName = null, $seoKeyName = null) {
                return $this->context->getProjectRouting()->generate($obj, $params, $routeName, $seoKeyName, UrlGeneratorInterface::ABSOLUTE_PATH);
            }),
            new \Twig_SimpleFunction('pum_url', function ($obj, array $params = array(), $routeName = null, $seoKeyName = null) {
                return $this->context->getProjectRouting()->generate($obj, $params, $routeName, $seoKeyName, UrlGeneratorInterface::ABSOLUTE_URL);
            }),
            new \Twig_SimpleFunction('pum_var', function ($key, $default = null) {
                try {
                    return $this->context->getProjectVars()->getValue($key, $default);
                } catch (\Exception $e) {
                    return $default;
                }
            }),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'pum_ucfirst'                     => new \Twig_Filter_Method($this, 'ucfirstFilter'),
            'pum_humanize_project_name'       => new \Twig_Filter_Method($this, 'humanizeProjectNameFilter'),
            'pum_humanize_beam_name'          => new \Twig_Filter_Method($this, 'humanizeBeamNameFilter'),
            'pum_humanize_object_name'        => new \Twig_Filter_Method($this, 'humanizeObjectNameFilter'),
            'pum_humanize_object_description' => new \Twig_Filter_Method($this, 'humanizeObjectDescriptionFilter'),
            'pum_replace'                     => new \Twig_Filter_Method($this, 'replaceFilter'),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function translateSchema($translate, $default = null)
    {
        if (!$default) {
            $default = $translate;
        }
        
        $translated = $this->getTranslator()->trans($translate, array(), 'pum_schema');
        
        if ($translated === $translate) {
            return ucfirst(trim(strtolower(preg_replace(array('/([A-Z])/', '/[_\s]+/'), array('_$1', ' '), $default))));
        }
        
        return $translated;
    }

    /**
     * {@inheritdoc}
     */
    public function humanizeProjectNameFilter($project)
    {
        if ($project instanceof \Pum\Core\Definition\Project) {
            return $this->translateSchema($project->getName());
        }
        
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function humanizeBeamNameFilter($beam)
    {
        if ($beam instanceof \Pum\Core\Definition\Beam) {
            return $this->translateSchema($beam->getName(), $beam->getAlias());
        }
        
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function humanizeObjectNameFilter($object)
    {
        if ($object instanceof \Pum\Core\Definition\ObjectDefinition) {
            return $this->translateSchema($object->getName(), $object->getAlias());
        }
        
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function humanizeObjectDescriptionFilter($object)
    {
        if ($object instanceof \Pum\Core\Definition\ObjectDefinition) {
            return $this->translateSchema($object->getDescription());
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function ucfirstFilter($input)
    {
        return ucfirst($input);
    }

    /**
     * {@inheritdoc}
     */
    public function replaceFilter($string, $patterns, $replacements)
    {
        return preg_replace($patterns, $replacements, $string);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pum';
    }
}
