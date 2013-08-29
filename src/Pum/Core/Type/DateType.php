<?php

namespace Pum\Core\Type;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Pum\Core\Validator\Constraints\Date as DateConstraints;
use Pum\Core\Type\DateType as Date;
use Doctrine\ORM\QueryBuilder;

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
    public function buildOptionsForm(FormInterface $form)
    {
        $form
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
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
        $options = $this->resolveOptions($options);

        $metadata->mapField(array(
            'fieldName' => $name,
            'type'      => 'date',
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

        $metadata->addGetterConstraint($name, new DateConstraints(array('restriction' => $options['restriction'])));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
        $options = $this->resolveOptions($options);

        if ($options['restriction'] === Date::ANTERIOR_DATE) {
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

        $form->add($name, 'date', array(
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
}
