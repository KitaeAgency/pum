<?php

namespace Pum\Core\Type;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;

class TextType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, FieldDefinition $definition)
    {
        $metadata->mapField(array(
            'fieldName' => $definition->getName(),
            'type'      => 'string',
            'length'    => $definition->getTypeOption('length', 100),
            'nullable'  => true,
        ));
    }

    public function getFormOptionsType()
    {
        return 'ww_field_type_text';
    }
}
