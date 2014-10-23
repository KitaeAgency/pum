<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Widget\Widgets;

use Pum\Bundle\ProjectAdminBundle\Extension\Widget\Widget;
use Pum\Bundle\CoreBundle\PumContext;
use Doctrine\Common\Collections\ArrayCollection;

class Beams extends ArrayCollection
{
    const WEIGHT = 5;

    public function __construct(PumContext $context)
    {
        parent::__construct();

        $project = $context->getProject();

        if (null !== $project) {
            foreach ($project->getBeamsOrderBy('name') as $beam) {
                $widget = Widget::create($beam->getName(), $beam->getIcon(), $beam->getColor(), self::WEIGHT)
                    ->setRoute('pa_beam_show', array('beamName' => $beam->getName()))
                    ->setPermission('PUM_OBJ_VIEW', array('project' => $project->getName(), 'beam' => $beam->getName()))
                    ->setUid('beam_'.$beam->getName())
                ;

                $this->add($widget);
            }
        }
    }
}
