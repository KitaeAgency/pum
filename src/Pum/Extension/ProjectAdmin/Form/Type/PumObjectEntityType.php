<?php

namespace Pum\Extension\ProjectAdmin\Form\Type;

use Pum\Core\ObjectFactory;
use Pum\Extension\EmFactory\EmFactory;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PumObjectEntityType extends AbstractType
{
    protected $objectFactory;
    protected $emFactory;

    public function __construct(ObjectFactory $objectFactory, EmFactory $emFactory)
    {
        $this->objectFactory = $objectFactory;
        $this->emFactory     = $emFactory;
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
            'em'       => function (Options $options) {
                return $this->emFactory->getManager($this->objectFactory, $options['project']);
            }
        ));

        $resolver->setRequired(array('project'));
    }

    public function getName()
    {
        return 'pum_object_entity';
    }
}
