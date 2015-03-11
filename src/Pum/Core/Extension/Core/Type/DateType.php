<?php

namespace Pum\Core\Extension\Core\Type;

use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Doctrine\ORM\QueryBuilder;
use Pum\Core\Definition\View\FormViewField;
use Pum\Core\Extension\Validation\Constraints\Date as DateTimeConstraints;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidationClassMetadata;

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
            '_doctrine_type'   => 'date',
            'unique'           => false,
            'restriction'      => null,
            'required'         => false,
            'label'            => null,
            'placeholder'      => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('unique', 'checkbox', array('required' => false))
            ->add('required', 'checkbox', array('required' => false))
            ->add('restriction', 'choice', array(
                    'required' => false,
                    'choices'   => array(
                            self::ANTERIOR_DATE  => 'pum.form.field.type.name.restriction.values.date.' . self::ANTERIOR_DATE,
                            self::POSTERIOR_DATE => 'pum.form.field.type.name.restriction.values.date.' . self::POSTERIOR_DATE
                    ),
                    'placeholder' => 'pum.form.field.type.name.restriction.values.date.emptyvalue'
            ))
        ;
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
    public function buildField(FieldBuildContext $context)
    {
        $cb = $context->getClassBuilder();
        $camel = $context->getField()->getCamelCaseName();

        $cb->createProperty($camel);

        $cb->createMethod('get'.ucfirst($camel), '', '
            return $this->'.$camel.';
        ');

        $cb->createMethod('set'.ucfirst($camel), '\DateTime $'.$camel.' = null', '
            $this->'.$camel.' = $'.$camel.';

            return $this;
        ');
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineField(FieldContext $context, ClassMetadata $metadata)
    {
        $metadata->mapField(array(
            'columnName' => $context->getField()->getLowercaseName(),
            'fieldName' => $context->getField()->getCamelCaseName(),
            'type'      => $context->getOption('_doctrine_type'),
            'nullable'  => true,
            'unique'    => $context->getOption('unique'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(FieldContext $context, ValidationClassMetadata $metadata)
    {
        $metadata->addGetterConstraint($context->getField()->getCamelCaseName(), new DateTimeConstraints(array('restriction' => $context->getOption('restriction'))));
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
    public function getName()
    {
        return 'date';
    }
}
