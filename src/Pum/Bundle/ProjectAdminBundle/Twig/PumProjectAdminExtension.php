<?php

namespace Pum\Bundle\ProjectAdminBundle\Twig;

use Pum\Bundle\CoreBundle\PumContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PumProjectAdminExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction('pum_pa_widget', function () {
                return $this->context->getContainer()->get('pum.project.admin.widgets');
            }),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pum_pa';
    }
}
