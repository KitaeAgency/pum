<?php

namespace Pum\Bundle\CoreBundle\DataFixtures\ORM\Config;

use Pum\Bundle\CoreBundle\DataFixtures\ORM\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadConfigData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $config = $this->get('pum.config');

        $values = array(
            'ww_reverse_seo_object_template_handler'    => true,
            'ww_show_export_import_button'              => true,
            'ww_show_clone_button'                      => true,
            'pa_default_pagination'                     => 10,
            'pa_pagination_values'                      => array(25, 50, 75, 100, 250, 500, 1000),
            'pa_default_tableview_truncatecols_value'   => 6,
            'pa_disable_default_tableview_truncatecols' => false,
            'ty_allowed_extra_type'                     => true,
        );

        foreach ($values as $key => $value) {
            $config->set($key, $value);
        }

        $config->flush();
    }
}
