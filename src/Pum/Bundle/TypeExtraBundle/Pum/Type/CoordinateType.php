<?php

namespace Pum\Bundle\TypeExtraBundle\Pum\Type;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Pum\Bundle\TypeExtraBundle\Model\Coordinate;
use Pum\Bundle\TypeExtraBundle\Validator\Constraints\Coordinate as CoordinateConstraints;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldContext;
use Pum\Core\Object\Object;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class CoordinateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('unique', 'checkbox', array('required' => false))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilterForm(FieldContext $context, FormBuilderInterface $builder)
    {
        $filterTypes = array(null, '=', '<', '<=', '<>', '>', '>=');
        $filterNames = array('Choose an operator', 'equal', 'inferior', 'inferior or equal', 'different', 'superior', 'superior or equal');

        $form
            ->add('value', 'text', array(
                'attr' => array('placeholder' => 'Currently, no filter on this column'),
                'disabled'    => true
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineField(FieldContext $context, DoctrineClassMetadata $metadata)
    {
        $name = $context->getField()->getLowercaseName();

        $metadata->mapField(array(
            'fieldName' => $name.'_lat',
            'type'      => 'decimal',
            'precision' => 9,
            'scale'     => 7,
            'nullable'  => true,
        ));

        $metadata->mapField(array(
            'fieldName' => $name.'_lng',
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
        $metadata->addGetterConstraint($context->getField->getCamelCaseName(), new CoordinateConstraints());
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FieldContext $context, FormBuilderInterface $builder)
    {
        $builder->add($name, 'pum_coordinate', array('label' => ucfirst($name)));
    }

    /**
     * @return QueryBuilder;
     */
    public function addOrderCriteria(FieldContext $context, QueryBuilder $qb, $order)
    {
        $field = $qb->getRootAlias() . '.' . $name.'_lat';

        $qb->orderby($field, $order);

        return $qb;
    }

    public function getName()
    {
        return 'coordinate';
    }
}
