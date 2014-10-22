<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Widget\Collection;

use Pum\Bundle\ProjectAdminBundle\Extension\Widget\Widget;
use Pum\Bundle\CoreBundle\PumContext;
use Doctrine\Common\Collections\ArrayCollection;

class Beam extends ArrayCollection {
	public function __construct(PumContext $context) {
		parent::__construct();

		$project = $context->getProject();

		if ($project) {
			foreach ($project->getBeams() as $beam) {
				$widget = new Widget($beam->getName(), $beam->getIcon(), $beam->getColor());
				$widget->setRoute('pa_beam_show', array('beamName' => $beam->getName()));
				$widget->setPermission('PUM_OBJ_VIEW', array('project' => $project->getName(), 'beam' => $beam->getName()));
				$widget->setWeight(0);

				$this->add($widget);
			}
		}
	}
}