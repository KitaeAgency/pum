<?php

namespace Pum\Extension\Core\Type;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldContext;
use Pum\Core\Validator\Constraints\Date as DateConstraint;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class DateType extends AbstractType
{
    const DATE_FORMAT    = "dd/MM/yyyy";
    const JS_DATE_FORMAT = "dd/mm/yy";

    const ANTERIOR_DATE  = 'anterior';
    const POSTERIOR_DATE = 'posterior';

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'unique'           => false,
            'restriction'      => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('unique', 'checkbox', array('required' => false))
            ->add('restriction', 'choice', array(
                    'required' => false,
                    'choices'   => array(
                            self::ANTERIOR_DATE  => 'Allow only anterior date',
                            self::POSTERIOR_DATE => 'Allow only posterior date'
                    ),
                    'empty_value' => 'No restriction',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(FieldContext $context, DoctrineClassMetadata $metadata)
    {
        $metadata->mapField(array(
            'fieldName' => $name,
            'type'      => 'date',
            'nullable'  => true,
            'unique'    => $context->getOption('unique'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        $options = $this->resolveOptions($options);

        $metadata->addGetterConstraint($name, new DateConstraint(array('restriction' => $options['restriction'])));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FieldContext $context, FormBuilderInterface $builder)
    {
        $restriction = $context->getOption('restriction');

        if ($restriction === Date::ANTERIOR_DATE) {
            $yearsRange = "-70:+0";
            $minDate = new \DateTime("-70 years");
            $maxDate = new \DateTime();
        } elseif ($options['restriction'] === Date::POSTERIOR_DATE) {
            $yearsRange = "-0:+70";
            $minDate = new \DateTime();
            $maxDate = new \DateTime("+70 years");
        } else {
            $yearsRange = "-35:+35";
            $minDate = new \DateTime("-35 years");
            $maxDate = new \DateTime("+35 years");
        }

        $builder->add($name, 'date', array(
            'widget' => 'single_text',
            'format' => self::DATE_FORMAT,
            'attr' => array(
                'class' => 'datepicker',
                'data-yearrange' => $yearsRange,
                'data-mindate'     => $minDate->format("U"),
                'data-maxdate'     => $maxDate->format("U"),
                'data-dateFormat'  => DateType::JS_DATE_FORMAT
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'date';
    }
}
