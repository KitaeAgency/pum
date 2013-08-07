<?php

namespace Pum\Bundle\TypeExtraBundle\Pum\Type;

use Pum\Bundle\TypeExtraBundle\Model\Price;
use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Pum\Core\Object\Object;
use Pum\Core\Type\AbstractType;

class PriceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
        $metadata->mapField(array(
            'fieldName' => $name.'_value',
            'type'      => 'decimal',
            'precision' => 19,
            'scale'     => 4,
            'nullable'  => true,
        ));

        $metadata->mapField(array(
            'fieldName' => $name.'_currency',
            'type'      => 'text',
            'length'    => 5,
            'nullable'  => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function writeValue(Object $object, $value, $name, array $options)
    {
        if (null === $value) {
            $object->__pum__rawSet($name.'_value', null);
            $object->__pum__rawSet($name.'_currency', null);
        }

        if (!$value instanceof Price) {
            throw new \InvalidArgumentException(sprintf('Expected a Price, got a "%s".', is_object($value) ? get_class($value) : gettype($value)));
        }

        $object->__pum__rawSet($name.'_value', $value->getValue());
        $object->__pum__rawSet($name.'_currency', $value->getCurrency());
    }

    /**
     * {@inheritdoc}
     */
    public function readValue(Object $object, $name, array $options)
    {
        $value    = $object->__pum__rawGet($name.'_value');
        $currency = $object->__pum__rawGet($name.'_currency');

        if (null === $value && null === $currency) {
            return null;
        }

        return new Price($value, $currency);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptionsType()
    {
        return 'ww_field_type_price';
    }
}
