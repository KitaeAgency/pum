<?php

namespace Pum\Bundle\TypeExtraBundle\Pum\Type;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Pum\Bundle\TypeExtraBundle\Model\Price;
use Pum\Bundle\TypeExtraBundle\Validator\Constraints\Price as PriceConstraint;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\View\FormViewField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidationClassMetadata;

class PriceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'currency'    => "EUR",
            'negative'    => false,
            'precision'   => 19,
            'scale'       => 4,
            'label'       => null,
            'placeholder' => null,
            'required'    => false
        ));
    }

    /**
     * @return array
     */
    protected function getCurrencies()
    {
        $currencies = array(
            'EUR' => 'pum.form.field.type.name.currency.currencies.eur',
            'USD' => 'pum.form.field.type.name.currency.currencies.usd'
        );

        return $currencies;
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('currency', 'choice', array(
                    'choices'   => $this->getCurrencies(),
                    'empty_value' => true
            ))
            ->add('negative', 'checkbox', array('required' => false))
            ->add('precision', 'number', array('required' => false))
            ->add('scale', 'number', array('required' => false))
            ->add('required', 'checkbox', array('required' => false))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilterForm(FormBuilderInterface $builder)
    {
        $filterTypes = array('=', '<', '<=', '<>', '>', '>=');
        $filterNames = array('equal', 'inferior', 'inferior or equal', 'different', 'superior', 'superior or equal');

        $builder
            ->add('type', 'choice', array(
                'choices'  => array_combine($filterTypes, $filterNames)
            ))
            ->add('value', 'number')
            /*->add('currency', 'choice', array(
                'choices'  => array_merge(array(null => 'All currencies'), $this->getCurrencies())
            ))*/
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildField(FieldBuildContext $context)
    {
        $cb = $context->getClassBuilder();
        $camel = $context->getField()->getCamelCaseName();

        $cb->createProperty($camel.'_value');
        $cb->createProperty($camel.'_currency');

        $cb->createMethod('get'.ucfirst($camel), '', '
            if (null === $this->'.$camel.'_value) {
                return null;
            }

            return new \Pum\Bundle\TypeExtraBundle\Model\Price($this->'.$camel.'_value, $this->'.$camel.'_currency);
        ');

        $cb->createMethod('set'.ucfirst($camel), '\Pum\Bundle\TypeExtraBundle\Model\Price $'.$camel, '
            $this->'.$camel.'_value    = $'.$camel.'->getValue();
            $this->'.$camel.'_currency = $'.$camel.'->getCurrency();

            return $this;
        ');
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineField(FieldContext $context, ClassMetadata $metadata)
    {
        $metadata->mapField(array(
            'columnName' => $context->getField()->getLowercaseName().'_value',
            'fieldName' => $context->getField()->getCamelCaseName().'_value',
            'type'      => 'decimal',
            'precision' => $context->getOption('precision'),
            'scale'     => $context->getOption('scale'),
            'nullable'  => true,
        ));

        $metadata->mapField(array(
            'columnName' => $context->getField()->getLowercaseName().'_currency',
            'fieldName' => $context->getField()->getCamelCaseName().'_currency',
            'type'      => 'string',
            'length'    => 5,
            'nullable'  => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(FieldContext $context, ValidationClassMetadata $metadata)
    {
        $allowNegativePrice = $context->getOption('negative');

        $metadata->addGetterConstraint($context->getField()->getCamelCaseName(), new PriceConstraint(array('allowNegativePrice' => $allowNegativePrice)));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FieldContext $context, FormInterface $form, FormViewField $formViewField)
    {
        $form->add($context->getField()->getCamelCaseName(), 'pum_price', array(
            'label' => $formViewField->getLabel(),
            'attr'  => array(
                'placeholder' => $formViewField->getPlaceholder()
            ),
            'required' => $context->getOption('required'),
            'disabled' => $formViewField->getDisabled(),
        ));
    }

    /**
     * @return QueryBuilder;
     */
    public function addOrderCriteria(FieldContext $context, QueryBuilder $qb, $order)
    {
        $field = $qb->getRootAlias() . '.' . $context->getField()->getCamelCaseName().'_value';

        $qb->orderby($field, $order);

        return $qb;
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

        $name = $context->getField()->getCamelCaseName();

        $parameterKey = count($qb->getParameters());

        $qb
            ->andWhere($qb->getRootAlias().'.'.$name.'_value'.' '.$filter['type'].' ?'.$parameterKey)
            ->setParameter($parameterKey, $filter['value']);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'price';
    }
}
