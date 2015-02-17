<?php

namespace Pum\Bundle\TypeExtraBundle\Pum\Type;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Pum\Bundle\TypeExtraBundle\Model\Coordinate;
use Pum\Bundle\TypeExtraBundle\Validator\Constraints\Coordinate as CoordinateConstraints;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\View\FormViewField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class CoordinateType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'unique'   => false,
            'required' => false,
            'label'    => null
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
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilterForm(FormBuilderInterface $builder)
    {
        $filterTypes = array('<', '<=', '<>', '>', '>=');
        $filterNames = array('pa.form.tableview.columns.entry.filters.entry.type.types.equal', 'pa.form.tableview.columns.entry.filters.entry.type.types.inferior', 'pa.form.tableview.columns.entry.filters.entry.type.types.inferior_or_equal', 'pa.form.tableview.columns.entry.filters.entry.type.types.different', 'pa.form.tableview.columns.entry.filters.entry.type.types.superior', 'pa.form.tableview.columns.entry.filters.entry.type.types.superior_or_equal');

        $builder
            ->add('value', 'text', array(
                // 'attr'     => array('placeholder' => 'Currently, no filter on this column'),
                'attr'     => array('placeholder' => 'pa.form.tableview.columns.entry.filters.entry.value.placeholder'),
                'mapped'   => false,
                'disabled' => true
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

        $cb->createProperty($camel.'_lat');
        $cb->createProperty($camel.'_lon');

        $cb->createMethod('get'.ucfirst($camel), '', '
            if (null === $this->'.$camel.'_lat && null === $this->'.$camel.'_lon) {
                return null;
            }

            return new \Pum\Bundle\TypeExtraBundle\Model\Coordinate($this->'.$camel.'_lat, $this->'.$camel.'_lon);
        ');

        $cb->createMethod('set'.ucfirst($camel), '\Pum\Bundle\TypeExtraBundle\Model\Coordinate $'.$camel, '
            $this->'.$camel.'_lat = $'.$camel.'->getLat();
            $this->'.$camel.'_lon = $'.$camel.'->getLng();
        ');
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineField(FieldContext $context, DoctrineClassMetadata $metadata)
    {
        $metadata->mapField(array(
            'columnName' => $context->getField()->getLowercaseName().'_lat',
            'fieldName' => $context->getField()->getCamelCaseName().'_lat',
            'type'      => 'decimal',
            'precision' => 9,
            'scale'     => 7,
            'nullable'  => true,
        ));

        $metadata->mapField(array(
            'columnName' => $context->getField()->getLowercaseName().'_lon',
            'fieldName' => $context->getField()->getCamelCaseName().'_lon',
            'type'      => 'decimal',
            'precision' => 10,
            'scale'     => 7,
            'nullable'  => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(FieldContext $context, ClassMetadata $metadata)
    {
        $metadata->addGetterConstraint($context->getField()->getCamelCaseName(), new CoordinateConstraints());
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FieldContext $context, FormBuilderInterface $form, FormViewField $formViewField)
    {
        $form->add($context->getField()->getCamelCaseName(), 'pum_coordinate', array(
            'label'    => $formViewField->getLabel(),
            'required' => $context->getOption('required'),
            'disabled' => $formViewField->getDisabled(),
        ));
    }

    /**
     * @return QueryBuilder;
     */
    public function addOrderCriteria(FieldContext $context, QueryBuilder $qb, $order)
    {
        $field = $qb->getRootAlias() . '.' . $context->getField()->getLowercaseName().'_lat';

        $qb->orderby($field, $order);

        return $qb;
    }

    public function getName()
    {
        return 'coordinate';
    }
}
