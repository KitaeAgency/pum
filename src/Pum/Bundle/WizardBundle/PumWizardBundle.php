<?php

namespace Pum\Bundle\WizardBundle;

use Pum\Bundle\WizardBundle\Configurator\Step\PumStep;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PumWizardBundle extends Bundle
{
    public function boot()
    {
        $configurator = $this->container->get('sensio_distribution.webconfigurator');
        $configurator->addStep(new PumStep($configurator->getParameters()));
    }
}
