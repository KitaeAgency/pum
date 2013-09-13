<?php

namespace Pum\Bundle\TypeExtraBundle\Pum\Type;

use Pum\Core\Type\AbstractType;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class HtmlType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'required'   => false,
            'unique'     => false,
            'is_inline'  => false, // block (<p>, <div>....) --- inline (<br />)
            'min_length' => 0,
            'max_length' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormInterface $form)
    {
        $form
            ->add('is_inline', 'checkbox', array('required' => false))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
        $options = $this->resolveOptions($options);

        $metadata->mapField(array(
            'fieldName' => $name,
            'type'      => 'text',
            'nullable'  => true,
            'unique'    => $options['unique'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        $options = $this->resolveOptions($options);

        if ($options['required']) {
            $metadata->addGetterConstraint($name, new NotBlank());
        }

        if ($options['min_length'] || $options['max_length']) {
            $metadata->addGetterConstraint($name, new Length(array('min' => $options['min_length'], 'max' => $options['max_length'])));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
        $options = $this->resolveOptions($options);

        if ($options['is_inline']) {
            $toolbar = array(
                array('Bold', 'Italic', 'Link')
            );
        } else {
            $toolbar = array(
                array('Styles', 'Table')
            );
        }

        $ckeditorConfig = array(
            'toolbar' => $toolbar,
            'customConfig' => '', # disable dynamic config.js loading
        );

        $form->add($name, 'textarea', array(
            'attr' => array('data-ckeditor'=> json_encode($ckeditorConfig))
        ));
    }
}
