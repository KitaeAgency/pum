<?php

namespace Pum\Bundle\CoreBundle\Twig;

use Pum\Bundle\CoreBundle\PumContext;

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
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pum';
    }
}
