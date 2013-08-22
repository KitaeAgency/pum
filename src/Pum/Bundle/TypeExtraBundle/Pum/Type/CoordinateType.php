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

class CoordinateType extends AbstractType
{
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
    public function writeValue(Object $object, $value, $name, array $options)
    {
        if (null === $value) {
            $object->_pumRawSet($name.'_lat', null);
            $object->_pumRawSet($name.'_lng', null);
        }

        if (!$value instanceof Coordinate) {
            throw new \InvalidArgumentException(sprintf('Expected a Coordinate, got a "%s".', is_object($value) ? get_class($value) : gettype($value)));
        }

        $object->_pumRawSet($name.'_lat', $value->getLat());
        $object->_pumRawSet($name.'_lng', $value->getLng());
    }

    /**
     * {@inheritdoc}
     */
    public function readValue(Object $object, $name, array $options)
    {
        $lat = $object->_pumRawGet($name.'_lat');
        $lng = $object->_pumRawGet($name.'_lng');

        if (null === $lat && null === $lng) {
            return null;
        }

        return new Coordinate($lat, $lng);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptionsType()
    {
        return 'ww_field_type_coordinate';
    }

    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        $metadata->addGetterConstraint($name, new CoordinateConstraints());
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
        $form->add($name.'_lat', 'text', array('label' => ucfirst($name) . " latitude"));
        $form->add($name.'_lng', 'text', array('label' => ucfirst($name) . " longitude"));
    }
}
