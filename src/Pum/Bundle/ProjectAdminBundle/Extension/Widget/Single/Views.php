<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Widget\Single;

use Pum\Bundle\ProjectAdminBundle\Extension\Widget\Widget;
use Symfony\Component\Security\Core\SecurityContext;

class Views extends Widget {
	public function __construct(SecurityContext $security) {
		parent::__construct('common.custom.view.leftnav', 'pictures', 'concrete');
		$this->setWeight(0);

		if ($security->isGranted('ROLE_PA_CUSTOM_VIEWS')) {
			$this->setRoute('pa_custom_view_index');
			$this->setPermission('ROLE_PA_CUSTOM_VIEWS');
		} else {
			parent::__construct('common.custom.view.leftnav', 'pictures', 'concrete');
			$this->setRoute('pa_admin_custom_view_index');
			$this->setPermission('ROLE_PA_DEFAULT_VIEWS');
		}
	}
}