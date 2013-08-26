<?php

namespace Pum\Core\Type;

use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pum\Core\Validator\Constraints\DateTime as DateTimeConstraints;
use Pum\Bundle\WoodworkBundle\Form\Type\FieldType\DateType as Date;

class DatetimeType extends AbstractType
{
    const DATETIME_FORMAT = "dd/MM/yyyy hh:mm a";
    const JS_TIME_FORMAT  = "hh:mm TT";

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
        $unique    = isset($options['unique']) ? $options['unique'] : false;

        $metadata->mapField(array(
            'fieldName' => $name,
            'type'      => 'datetime',
            'nullable'  => true,
            'unique'    => $unique,
        ));
    }

    public function getFormOptionsType()
    {
        return 'ww_field_type_datetime';
    }

    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        $metadata->addGetterConstraint($name, new DateTimeConstraints(array('restriction' => $options['restriction'])));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
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
            'format' => self::DATETIME_FORMAT,
            'attr' => array(
                'class' => 'datetimepicker',
                'data-yearrange' => $yearsRange, 
                'data-mindate'     => $minDate->format("U"),
                'data-maxdate'     => $maxDate->format("U"),
                'data-timeformat'  => self::JS_TIME_FORMAT,
                'data-dateFormat'  => DateType::JS_DATE_FORMAT
            )
        ));
    }
}
