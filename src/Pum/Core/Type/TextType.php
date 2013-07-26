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
        $type = ($definition->getTypeOption('multi_lines')) ? 'text' : 'string';
        $metadata->mapField(array(
            'fieldName' => $definition->getName(),
            'type'      => $type,
            'length'    => $definition->getTypeOption('length', 100),
            'nullable'  => true,
            'unique'    => $definition->getTypeOption('unique'),
        ));
    }

    public function getFormOptionsType()
    {
        return 'ww_field_type_text';
    }
}
