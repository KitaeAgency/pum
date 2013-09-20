<?php

namespace Pum\Bundle\TypeExtraBundle\Pum\Type;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Pum\Bundle\TypeExtraBundle\Model\Price;
use Pum\Bundle\TypeExtraBundle\Validator\Constraints\Price as PriceConstraint;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class PriceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'currency'  => "EUR",
            'negative'  => false,
            'precision' => 19,
            'scale'     => 4
        ));
    }

    /**
     * @return array
     */
    protected function getCurrencies()
    {
        $currencies = array('EUR', 'USD');

        return array_combine($currencies, $currencies);
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('currency', 'choice', array(
                    'choices'   => $this->getCurrencies(),
                    'empty_value' => 'Choose your currency',
            ))
            ->add('negative', 'checkbox', array('label' => 'Allow negative price'))
            ->add('precision', 'number', array('required' => false))
            ->add('scale', 'number', array('label' => 'Decimal', 'required' => false))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilterForm(FormBuilderInterface $builder)
    {
        $filterTypes = array(null, '=', '<', '<=', '<>', '>', '>=');
        $filterNames = array('Choose an operator', 'equal', 'inferior', 'inferior or equal', 'different', 'superior', 'superior or equal');

        $builder
            ->add('type', 'choice', array(
                'choices'  => array_combine($filterTypes, $filterNames)
            ))
            ->add('amount', 'number', array(
                'attr' => array('placeholder' => 'Amount')
            ))
            ->add('currency', 'choice', array(
                'choices'  => array_merge(array(null => 'All currencies'), $this->getCurrencies())
            ))
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
    public function mapDoctrineField(FieldContext $context, DoctrineClassMetadata $metadata)
    {
        $name = $context->getField()->getLowercaseName();

        $metadata->mapField(array(
            'fieldName' => $name.'_value',
            'type'      => 'decimal',
            'precision' => $context->getOption('precision'),
            'scale'     => $context->getOption('scale'),
            'nullable'  => true,
        ));

        $metadata->mapField(array(
            'fieldName' => $name.'_currency',
            'type'      => 'string',
            'length'    => 5,
            'nullable'  => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        $options = $this->resolveOptions($options);

        $metadata->addGetterConstraint($name, new PriceConstraint(array('allowNegativePrice' => $options['negative'])));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FieldContext $context, FormBuilderInterface $builder)
    {
        $builder->add($name, 'pum_price');
    }

    /**
     * @return QueryBuilder;
     */
    public function addOrderCriteria(FieldContext $context, QueryBuilder $qb, $order)
    {
        $field = $qb->getRootAlias() . '.' . $name.'_value';

        $qb->orderby($field, $order);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterCriteria(FieldContext $context, QueryBuilder $qb, $filter)
    {
        if (isset($values['type']) && isset($values['amount'])) {
            if (!is_null($values['type']) && !is_null($values['amount'])) {
                $parameterKey = count($qb->getParameters());

                $qb
                    ->andWhere($qb->getRootAlias().'.'.$name.'_value'.' '.$values['type'].' ?'.$parameterKey)
                    ->setParameter($parameterKey, $values['amount']);
            }
        }

        if (isset($values['currency']) && !is_null($values['currency'])) {
            $parameterKey = count($qb->getParameters());

            $qb
                ->andWhere($qb->getRootAlias().'.'.$name.'_currency'.' = ?'.$parameterKey)
                ->setParameter($parameterKey, $values['currency']);
        }

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
