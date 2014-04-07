<?php

namespace Pum\Core\Extension\Core\Type;

use Pum\Core\AbstractType;
use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\View\FormViewField;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;

class DatetimeType extends AbstractType
{
    const DATE_FORMAT    = "dd/MM/yyyy HH:mm:ss";
    const JS_DATE_FORMAT = "dd/mm/yy HH:ii:ss";

    const ANTERIOR_DATE  = 'anterior';
    const POSTERIOR_DATE = 'posterior';

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
    public function buildForm(FieldContext $context, FormInterface $form, FormViewField $formViewField)
    {
        $restriction = $context->getOption('restriction');

        if ($restriction === self::ANTERIOR_DATE) {
            $yearsRange = "-70:+0";
            $minDate = new \DateTime("-70 years");
            $maxDate = new \DateTime();
        } elseif ($restriction === self::POSTERIOR_DATE) {
            $yearsRange = "-0:+70";
            $minDate = new \DateTime();
            $maxDate = new \DateTime("+70 years");
        } else {
            $yearsRange = "-35:+35";
            $minDate = new \DateTime("-35 years");
            $maxDate = new \DateTime("+35 years");
        }

        $form->add($context->getField()->getCamelCaseName(), 'date', array(
            'widget' => 'single_text',
            'format' => self::DATE_FORMAT,
            'attr'   => array(
                'class' => 'datepicker',
                'data-yearrange'  => $yearsRange,
                'data-mindate'    => $minDate->format("U"),
                'data-maxdate'    => $maxDate->format("U"),
                'data-dateFormat' => self::JS_DATE_FORMAT,
                'placeholder'     => $formViewField->getPlaceholder()
            ),
            'label'    => $formViewField->getLabel(),
            'required' => $context->getOption('required')
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
