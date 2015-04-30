<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Vars\VarsInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class VarImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', 'file', array(
                'constraints' => array(
                    new NotBlank(array()),
                ),
                'required' => false,
                'mapped'   => false
            ))
            ->add('delete_old', 'checkbox', array('required' => false))
            ->add('import', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'pum_form'
        ));
    }

    public function getName()
    {
        return 'pum_var_export';
    }

}
