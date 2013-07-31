<?php

namespace Pum\Core\Type;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;

class IntegerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, FieldDefinition $definition)
    {
        $metadata->mapField(array(
            'fieldName' => $definition->getName(),
            'type'      => 'integer',
            'nullable'  => true,
            'unique'    => $definition->getTypeOption('unique'),
        ));
    }

    public function getFormOptionsType()
    {
        return 'ww_field_type_integer';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'number';
    }
}
