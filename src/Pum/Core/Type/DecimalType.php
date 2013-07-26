<?php

namespace Pum\Core\Type;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;

class DecimalType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, FieldDefinition $definition)
    {
        $metadata->mapField(array(
            'fieldName' => $definition->getName(),
            'type'      => 'decimal',
            'nullable'  => true,
            'unique'    => $definition->getTypeOption('unique'),
            'precision' => $definition->getTypeOption('precision', 18),
            'scale'     => $definition->getTypeOption('scale', 0),
        ));
    }

    public function getFormOptionsType()
    {
        return 'ww_field_type_decimal';
    }
}
