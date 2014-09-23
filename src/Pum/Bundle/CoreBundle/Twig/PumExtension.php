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
            new \Twig_SimpleFunction('pum_var', function ($key) {
                return $this->context->getProjectVars()->getValue($key);
            }),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'pum_ucfirst' => new \Twig_Filter_Method($this, 'ucfirstFilter')
        );
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
    public function getName()
    {
        return 'pum';
    }
}
