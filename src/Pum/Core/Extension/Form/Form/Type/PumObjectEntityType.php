<?php

namespace Pum\Core\Extension\Form\Form\Type;

use Pum\Core\Extension\EmFactory\EmFactoryExtension;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PumObjectEntityType extends AbstractType
{
    protected $emFactory;

    public function __construct(EmFactoryExtension $emFactory)
    {
        $this->emFactory = $emFactory;
    }

    public function getParent()
    {
        return 'entity';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // disable EM normalizer
        $resolver->setNormalizers(array('em' => function (Options $options, $val) { return $val; }));

        $resolver->setDefaults(array(
            'property' => 'title',
            'em'       => function (Options $options) {
                return $this->emFactory->getManager($options['project']);
            }
        ));

        $resolver->setRequired(array('project'));
    }

    public function getName()
    {
        return 'pum_object_entity';
    }
}
