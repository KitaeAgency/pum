<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Widget\Single;

use Pum\Bundle\ProjectAdminBundle\Extension\Widget\Widget;

class Variables extends Widget {
	public function __construct() {
		parent::__construct('common.vars.leftnav', 'settings', 'asbestos');
		$this->setRoute('pa_vars_index');
		$this->setPermission('ROLE_PA_VARS');

		$this->setWeight(0);
	}
}