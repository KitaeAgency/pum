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
            new \Twig_SimpleFunction('pum_projects', function ($accessOnly = false) {
                if ($accessOnly) {
                    $projects = $this->context->getAllProjects();
                    $filteredProjects = array();
                    foreach ($projects as $project) {
                        if ($this->context->getContainer()->get('security.context')->isGranted('PUM_OBJ_VIEW', array('project' => $project->getName())) && $this->context->getProjectName() !== $project->getName()) {
                            $filteredProjects[] = $project;
                        }
                    }

                    return $filteredProjects;
                }

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
            new \Twig_SimpleFunction('pum_config', function ($key, $default = null) {
                $value = $this->context->getProjectConfig()->get($key);
                if (!$value) {
                    return $default;
                }
                return $value;
            })
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'pum_humanize'                    => new \Twig_Filter_Method($this, 'humanize'),
            'pum_ucfirst'                     => new \Twig_Filter_Method($this, 'ucfirstFilter'),
            'pum_initials'                    => new \Twig_Filter_Method($this, 'getInitials'),
            'pum_translate_schema'            => new \Twig_Filter_Method($this, 'translateSchema'),
            'pum_humanize_project_name'       => new \Twig_Filter_Method($this, 'humanizeProjectNameFilter'),
            'pum_humanize_beam_name'          => new \Twig_Filter_Method($this, 'humanizeBeamNameFilter'),
            'pum_humanize_object_name'        => new \Twig_Filter_Method($this, 'humanizeObjectNameFilter'),
            'pum_replace'                     => new \Twig_Filter_Method($this, 'replaceFilter'),
            'pum_highlight'                   => new \Twig_Filter_Method($this, 'highlight'),
        );
    }

    /**
     * Return alias if translation string is not defined
     * @param  string $translate the translate key to checked if translated
     * @param  string $default   the default string to return if not translated
     * @return string
     */
    public function translateSchema($translate, $default = null)
    {
        if (!$default) {
            $default = $translate;
        }

        $translated = $this->getTranslator()->trans($translate, array(), 'pum_schema');

        if ($translated === $translate) {
            if ($default) {
                return $this->humanize($default);
            }

            return $this->humanize($translate);
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
        } elseif (is_string($project)) {
            return $this->translateSchema($project);
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
        } elseif (is_string($beam)) {
            return $this->translateSchema($beam);
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
        } elseif (is_string($object)) {
            return $this->translateSchema($object);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function humanize($input)
    {
        return ucfirst(trim(preg_replace(array('/[_\s]+/'), array(' '), $input)));
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
    public function getInitials($input)
    {
        preg_match_all('/\b\w/u', $input, $matches);

        return implode('', $matches[0]);
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
    public function highlight($text, $search, $highlightColor = '#31beb1', $casesensitive = false)
    {
        $search         = preg_replace('!\s+!', ' ', $search);
        $modifier       = ($casesensitive) ? '' : 'i';
        $strReplacement = '$0';
        $words          = explode(' ', $search);

        foreach ($words as $word) {
            if ($word) {
                $quotedSearch = preg_quote($word, '/');
                $checkPattern = '/'.$quotedSearch.'/'.$modifier;
                $text         = preg_replace($checkPattern, sprintf('<strong style="color: %s">'.$strReplacement.'</strong>', $highlightColor), $text);
            }
        }

        return $text;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pum';
    }
}
