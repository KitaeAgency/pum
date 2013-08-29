<?php

namespace Pum\Bundle\TypeExtraBundle\Pum\Type;

use Pum\Bundle\TypeExtraBundle\Model\Coordinate;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Pum\Core\Object\Object;
use Pum\Core\Type\AbstractType;
use Pum\Bundle\TypeExtraBundle\Validator\Constraints\Coordinate as CoordinateConstraints;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;

class CoordinateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormInterface $form)
    {
        $form
            ->add('unique', 'checkbox', array('required' => false))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
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
    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        $metadata->addGetterConstraint($name, new CoordinateConstraints());
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
        $form->add($name, 'pum_coordinate', array('label' => ucfirst($name)));
    }

    /**
     * {@inheritdoc}
     */
    public function writeValue(Object $object, $value, $name, array $options)
    {
        if (null === $value) {
            $object->set($name.'_lat', null);
            $object->set($name.'_lng', null);
        }

        if (!$value instanceof Coordinate) {
            throw new \InvalidArgumentException(sprintf('Expected a Coordinate, got a "%s".', is_object($value) ? get_class($value) : gettype($value)));
        }

        $object->set($name.'_lat', $value->getLat());
        $object->set($name.'_lng', $value->getLng());
    }

    /**
     * {@inheritdoc}
     */
    public function readValue(Object $object, $name, array $options)
    {
        $lat = $object->get($name.'_lat');
        $lng = $object->get($name.'_lng');

        return new Coordinate($lat, $lng);
    }

    /**
     * {@inheritdoc}
     */
    public function getRawColumns($name, array $options)
    {
        return array($name.'_lat', $name.'_lng');
    }

    /**
     * @return QueryBuilder;
     */
    public function addOrderCriteria(QueryBuilder $qb, $name, array $options, $order)
    {
        $field = $qb->getRootAlias() . '.' . $name.'_lat';

        $qb->orderby($field, $order);

        return $qb;
    }
}
