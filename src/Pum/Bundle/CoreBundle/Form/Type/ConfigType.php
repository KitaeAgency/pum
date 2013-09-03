<?php

namespace Pum\Bundle\CoreBundle\Form\Type;

use Pum\Core\Config\Config;
use Pum\Bundle\CoreBundle\Form\Listener\ConfigTypeListener;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfigType extends AbstractType
{
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new ConfigTypeListener($this->config));
    }

    public function getName()
    {
        return 'pum_config';
    }
}
