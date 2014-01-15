<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Core\SchemaManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

class BeamEntityType extends AbstractType
{
    protected $schemaManager;

    public function __construct(SchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'choice_list'   => new ObjectChoiceList($this->schemaManager->getAllBeams(), $label='name', array(), null, $value='name'),
            'translation_domain' => 'pum_form'
        ));
    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'ww_beam_entity';
    }
}
