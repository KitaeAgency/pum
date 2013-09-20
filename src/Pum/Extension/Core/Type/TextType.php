<?php

namespace Pum\Extension\Core\Type;

use Pum\Core\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TextType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'max_length' => null,
            'min_length' => null,
            'multilines' => true,
            'emf_length' => function (Options $options) {
                return $options['max_length'];
            },
            'validator_constraints' => function (Options $options) {
                $result = array();
                if ($options['required']) {
                    $result[] = new NotBlank();
                }

                if ($options['max_length'] || $options['min_length']) {
                    $result[] = new Length(array('min' => $options['min_length'], 'max' => $options['max_length']));
                }

                return $result;
            }
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'simple';
    }
}
