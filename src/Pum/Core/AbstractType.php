<?php

namespace Pum\Core;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Pum\Core\Extension\EmFactory\EmFactoryFeatureInterface;
use Pum\Core\Extension\ProjectAdmin\ProjectAdminFeatureInterface;
use Pum\Core\Extension\Validation\ValidationFeatureInterface;
use Pum\Core\Definition\View\FormViewField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

abstract class AbstractType implements TypeInterface, EmFactoryFeatureInterface, ProjectAdminFeatureInterface, ValidationFeatureInterface
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildField(FieldBuildContext $context)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineField(FieldContext $context, ClassMetadata $metadata)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormBuilderInterface $builder)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FieldContext $context, FormBuilderInterface $form, FormViewField $formViewField)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildFormViewOptions(FormBuilderInterface $builder, FormViewField $formViewField)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilterForm(FormBuilderInterface $builder)
    {
        $filterTypes = array('=', '<>');
        $filterNames = array(
            'pa.form.tableview.columns.entry.filters.entry.type.types.equal',
            'pa.form.tableview.columns.entry.filters.entry.type.types.different'
        );

        $builder
            ->add('type', 'choice', array(
                'choices' => array_combine($filterTypes, $filterNames)
            ))
            ->add('value', 'text')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function addOrderCriteria(FieldContext $context, QueryBuilder $qb, $order)
    {
        $by = $qb->getRootAlias() . '.' . $context->getField()->getCamelCaseName();
        $qb->orderby($by, $order);

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

        if (in_array($filter['type'], array('<', '>', '<=', '>=', '<>', '=', 'LIKE', 'NOT LIKE', 'BEGIN', 'END', 'IS NULL', 'IS NOT NULL'))) {
            $operator = $filter['type'];
        } else {
            throw new \InvalidArgumentException(sprintf('Unexpected filter type "%s".', $filter['type']));
        }

        switch ($filter['type']) {
            case 'BEGIN':
                $operator = 'LIKE';
                $value    = $filter['value'].'%';
                break;

            case 'END':
                $operator = 'LIKE';
                $value    = '%'.$filter['value'];
                break;

            case 'LIKE':
                $value = '%'.$filter['value'].'%';
                break;

            case 'NOT LIKE':
                $value = '%'.$filter['value'].'%';
                break;

            case 'IS NULL':
                $value = null;
                break;

            case 'IS NOT NULL':
                $value = null;
                break;

            default: $value = $filter['value'];
        }

        switch ($filter['type']) {

            return $qb
                ->andWhere($qb->getRootAlias().'.'.$context->getField()->getCamelCaseName().' '.$operator.' ?'.$parameterKey)
                ->setParameter($parameterKey, $value)
            ;
        } else {
            return $qb
                ->andWhere($qb->getRootAlias().'.'.$context->getField()->getCamelCaseName().' '.$operator);
            ;
                ;
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(FieldContext $context, ValidatorClassMetadata $metadata)
    {
    }
}
