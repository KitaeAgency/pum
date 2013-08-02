<?php

namespace Pum\Core\Type;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class TextType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'multi_lines'      => false,
            'unique'           => false,
            'required'         => false,
            'length'           => null,
            'min_length'       => 0,
            'max_length'       => null,
            '_doctrine_length' => function (Options $options) {
                return $options['max_length'] !== null ? $options['max_length'] : null;
            },
            '_doctrine_type' => function (Options $options) {
                return $options['max_length'] !== null ? 'string' : 'text';
            }
        ));
    }
    /**
     * {@inheritdoc}
     */
    public function getFormOptionsType()
    {
        return 'ww_field_type_text';
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
        $options = $this->resolveOptions($options);

        $metadata->mapField(array(
            'fieldName' => $name,
            'type'      => $options['_doctrine_type'],
            'length'    => $options['_doctrine_length'],
            'nullable'  => true,
            'unique'    => $options['unique'],
        ));
    }

    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        $options = $this->resolveOptions($options);

        if ($options['required']) {
            var_dump($options['required']);
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
        $form->add($name, 'text');
    }
}
