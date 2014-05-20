<?php

namespace Pum\Bundle\WizardBundle\Configurator\Step;

use Sensio\Bundle\DistributionBundle\Configurator\Step\StepInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class PumStep implements StepInterface
{
    public function __construct(array $parameters)
    {
    }

    /**
     * @see StepInterface
     */
    public function getFormType()
    {
        return new FormType();
    }

    /**
     * @see StepInterface
     */
    public function checkRequirements()
    {
        return array();
    }

    /**
     * @see StepInterface
     */
    public function checkOptionalSettings()
    {
        return array();
    }

    /**
     * @see StepInterface
     */
    public function update(StepInterface $data)
    {
        return array();
    }

    /**
     * @see StepInterface
     */
    public function getTemplate()
    {
        return 'PumWizardBundle:Configurator/Step:pum.html.twig';
    }
}
