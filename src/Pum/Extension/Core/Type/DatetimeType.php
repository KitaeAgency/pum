<?php

namespace Pum\Core\Type;

use Doctrine\ORM\QueryBuilder;
use Pum\Bundle\WoodworkBundle\Form\Type\FieldType\DateType as Date;
use Pum\Core\Context\FieldContext;
use Pum\Core\Type\DateType;
use Pum\Core\Validator\Constraints\DateTime as DateTimeConstraints;
use Pum\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class DatetimeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            '_doctrine_type' => 'datetime',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'datetime';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'date';
    }
}
