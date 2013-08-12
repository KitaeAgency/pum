<?php

namespace Pum\Bundle\TypeExtraBundle\Form\Type\FieldType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'choice', array(
                    'choices'   => array(
                        'image' => 'Image',
                        'video' => 'Video',
                        'pdf'   => 'PDF',
                        'file'  => 'File', 
                    ),
                    'empty_value' => 'Choose your type',
            ))
        ;
    }

    public function getName()
    {
        return 'ww_field_type_media';
    }
}
