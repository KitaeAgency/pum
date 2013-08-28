<?php

namespace Pum\Core\Type;

use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pum\Core\Validator\Constraints\DateTime as DateTimeConstraints;
use Pum\Bundle\WoodworkBundle\Form\Type\FieldType\DateType as Date;
use Doctrine\ORM\QueryBuilder;

class DatetimeType extends AbstractType
{
    const DATETIME_FORMAT = "dd/MM/yyyy hh:mm a";
    const JS_TIME_FORMAT  = "hh:mm TT";

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
                            DateType::ANTERIOR_DATE  => 'Allow only anterior date',
                            DateType::POSTERIOR_DATE => 'Allow only posterior date'
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
        $unique    = isset($options['unique']) ? $options['unique'] : false;

        $metadata->mapField(array(
            'fieldName' => $name,
            'type'      => 'datetime',
            'nullable'  => true,
            'unique'    => $unique,
        ));
    }

    /**
     * {@inheritdoc}
     */
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

        $form->add($name, 'datetime', array(
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

    /**
     * @return QueryBuilder;
     */
    public function addOrderCriteria(QueryBuilder $qb, $name, array $options, $order)
    {
        $field = $qb->getRootAlias() . '.' . $name;

        $qb->orderby($field, $order);

        return $qb;
    }
}
