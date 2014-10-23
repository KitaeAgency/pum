<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Widget\Widgets;

use Pum\Bundle\ProjectAdminBundle\Extension\Widget\Widget;

class Vars extends Widget
{
    const ICON   = 'settings';
    const COLOR  = 'asbestos';
    const WEIGHT = 15;

    public function __construct($name = 'pum_vars', $icon = self::ICON, $color= self::COLOR, $weight= self::WEIGHT)
    {
        parent::__construct($name, $icon, $color, $weight);

        $this
            ->setLabel('common.vars.leftnav')
            ->setRoute('pa_vars_index')
            ->setPermission('ROLE_PA_VARS')
        ;
    }
}
