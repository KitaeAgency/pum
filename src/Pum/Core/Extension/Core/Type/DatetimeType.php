<?php

namespace Pum\Core\Extension\Core\Type;

use Pum\Core\AbstractType;
use Pum\Core\Context\FieldContext;
use Doctrine\ORM\QueryBuilder;
use Pum\Core\Definition\View\FormViewField;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Pum\Core\Extension\Core\DataTransformer\ValueToDateIntervalTransformer;

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
    public function buildForm(FieldContext $context, FormBuilderInterface $form, FormViewField $formViewField)
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
            'required' => $context->getOption('required'),
            'disabled' => $formViewField->getDisabled(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterCriteria(FieldContext $context, QueryBuilder $qb, $filter)
    {
        if (!isset($filter['type']) || !$filter['type']) {
            return $qb;
        }
        if (!isset($filter['value'])) {
            return $qb;
        }

        if (in_array($filter['type'], array('<', '>', '<=', '>=', '<>', '='))) {
            $operator = $filter['type'];
        } else {
            throw new \InvalidArgumentException(sprintf('Unexpected filter type "%s".', $filter['type']));
        }

        $value = $filter['value'];

        if (is_string($value)) {
            $value = new \DateTime($value);
        }

        $parameterKey = count($qb->getParameters());

        return $qb
            ->andWhere($qb->getRootAlias().'.'.$context->getField()->getCamelCaseName().' '.$operator.' ?'.$parameterKey)
            ->setParameter($parameterKey, $value)
        ;
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
