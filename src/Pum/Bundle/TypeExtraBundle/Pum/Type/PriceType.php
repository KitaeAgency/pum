<?php

namespace Pum\Bundle\TypeExtraBundle\Pum\Type;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Pum\Bundle\TypeExtraBundle\Model\Price;
use Pum\Bundle\TypeExtraBundle\Validator\Constraints\Price as PriceConstraint;
use Pum\Core\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
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
            'scale'     => 4,
            '_doctrine_precision' => function (Options $options) {
                return $options['precision'] !== null ? $options['precision'] : 19;
            },
            '_doctrine_scale'     => function (Options $options) {
                return $options['scale'] !== null ? $options['scale'] : 4;
            }
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
    public function buildOptionsForm(FormInterface $form)
    {
        $form
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
    public function buildFormFilter(FieldDefinition $field, FormInterface $form)
    {
        $filterTypes = array(null, '=', '<', '<=', '<>', '>', '>=');
        $filterNames = array('Choose an operator', 'equal', 'inferior', 'inferior or equal', 'different', 'superior', 'superior or equal');

        $form
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
    public function mapDoctrineField(FieldDefinition $field, DoctrineClassMetadata $metadata)
    {
        $options = $this->resolveOptions($options);

        $metadata->mapField(array(
            'fieldName' => $name.'_value',
            'type'      => 'decimal',
            'precision' => $options['_doctrine_precision'],
            'scale'     => $options['_doctrine_scale'],
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
    public function buildForm(FieldDefinition $field, FormInterface $form)
    {
        $form->add($name, 'pum_price');
    }

    /**
     * @return QueryBuilder;
     */
    public function addOrderCriteria(FieldDefinition $field, QueryBuilder $qb, $name, array $options, $order)
    {
        $field = $qb->getRootAlias() . '.' . $name.'_value';

        $qb->orderby($field, $order);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterCriteria(QueryBuilder $qb, $name, array $values)
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
