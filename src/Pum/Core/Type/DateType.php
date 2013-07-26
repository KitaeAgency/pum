<?php

namespace Pum\Core\Type;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;

class DateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, FieldDefinition $definition)
    {
        $metadata->mapField(array(
            'fieldName' => $definition->getName(),
            'type'      => 'date',
            'nullable'  => true,
            'unique'    => $definition->getTypeOption('unique'),
        ));
    }

    public function getFormOptionsType()
    {
        return 'ww_field_type_date';
    }
}
