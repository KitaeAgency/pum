<?php

namespace Pum\Bundle\TypeExtraBundle\Pum\Type;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Pum\Bundle\TypeExtraBundle\Model\Coordinate;
use Pum\Bundle\TypeExtraBundle\Validator\Constraints\Coordinate as CoordinateConstraints;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
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
    public function buildFilterForm(FormBuilderInterface $builder)
    {
        $filterTypes = array('<', '<=', '<>', '>', '>=');
        $filterNames = array('equal', 'inferior', 'inferior or equal', 'different', 'superior', 'superior or equal');

        $builder
            ->add('value', 'text', array(
                'attr'     => array('placeholder' => 'Currently, no filter on this column'),
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
            if (null === $this->'.$camel.'_lat || null === $this->'.$camel.'_lon) {
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
        $name = $context->getField()->getLowercaseName();

        $metadata->mapField(array(
            'fieldName' => $name.'_lat',
            'type'      => 'decimal',
            'precision' => 9,
            'scale'     => 7,
            'nullable'  => true,
        ));

        $metadata->mapField(array(
            'fieldName' => $name.'_lon',
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
    public function buildForm(FieldContext $context, FormInterface $form)
    {
        $form->add($context->getField()->getCamelCaseName(), 'pum_coordinate');
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
