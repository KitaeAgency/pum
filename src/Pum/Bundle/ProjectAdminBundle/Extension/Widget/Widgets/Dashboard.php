<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Widget\Widgets;

use Pum\Bundle\ProjectAdminBundle\Extension\Widget\Widget;
use Symfony\Component\Security\Core\SecurityContext;

class Dashboard extends Widget
{
    const ICON   = 'meter3';
    const COLOR  = 'nephritis';
    const WEIGHT = 1;

    public function __construct($name = 'pum_dashboard', $icon = self::ICON, $color= self::COLOR, $weight= self::WEIGHT)
    {
        parent::__construct($name, $icon, $color, $weight);

        $this
            ->setLabel('common.dashboard.leftnav')
            ->setRoute('pa_homepage')
            ->setPermission('ROLE_PA_LIST')
        ;
    }
}
