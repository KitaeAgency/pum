<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;

class BeamImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('file', 'file', array(
                'constraints' => array(
                    new File(),
                    new NotBlank(array(
                        'message' => 'Please select a file'
                    ))
                ),
                'mapped' => false
            ))
            ->add('import', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Pum\Core\Definition\Beam',
            'validation_groups' => array('Import'),
            'translation_domain' => 'pum_form'
        ));
    }

    public function getName()
    {
        return 'ww_beam_import';
    }
}
