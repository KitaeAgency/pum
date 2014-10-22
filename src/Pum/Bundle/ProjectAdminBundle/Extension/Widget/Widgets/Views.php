<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Widget\Widgets;

use Pum\Bundle\ProjectAdminBundle\Extension\Widget\Widget;
use Symfony\Component\Security\Core\SecurityContext;

class Views extends Widget
{
    const ICON   = 'pictures';
    const COLOR  = 'concrete';
    const WEIGHT = 10;

    public function __construct(SecurityContext $security, $name = 'common.custom.view.leftnav', $icon = self::ICON, $color= self::COLOR, $weight= self::WEIGHT)
    {
        parent::__construct($name, $icon, $color, $weight);

        if ($security->isGranted('ROLE_PA_CUSTOM_VIEWS')) {
            $this
                ->setRoute('pa_custom_view_index')
                ->setPermission('ROLE_PA_CUSTOM_VIEWS')
            ;
        } elseif ($security->isGranted('ROLE_PA_DEFAULT_VIEWS')) {
            $this
                ->setRoute('pa_admin_custom_view_index')
                ->setPermission('ROLE_PA_DEFAULT_VIEWS')
            ;
        }
    }
}
