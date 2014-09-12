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
use Symfony\Component\Form\FormInterface;
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
    public function buildForm(FieldContext $context, FormInterface $form, FormViewField $formViewField)
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
        $filterNames = array('pa.form.tableview.columns.entry.filters.entry.type.types.equal', 'pa.form.tableview.columns.entry.filters.entry.type.types.different');

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

        if (in_array($filter['type'], array('<', '>', '<=', '>=', '<>', '=', 'LIKE', 'NOT LIKE', 'BEGIN', 'END'))) {
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

            default: $value = $filter['value'];
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
    public function mapValidation(FieldContext $context, ValidatorClassMetadata $metadata)
    {
    }
}
