<?php

namespace Pum\Bundle\CoreBundle\Form\Type;

use Pum\Bundle\CoreBundle\Form\Listener\ConfigTypeListener;
use Pum\Core\Config\ConfigInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfigType extends AbstractType
{
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tabs', 'pum_tabs');
        $builder->addEventSubscriber(new ConfigTypeListener($this->config));
    }

    public function getName()
    {
        return 'pum_config';
    }
}
