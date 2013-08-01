<?php

namespace Pum\Core\Type;

use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;

class DecimalType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
        $unique    = isset($options['unique']) ? $options['unique'] : false;
        $precision = isset($options['precision']) ? $options['precision'] : 18;
        $scale     = isset($options['scale']) ? $options['scale'] : 0;

        $metadata->mapField(array(
            'fieldName' => $name,
            'type'      => 'decimal',
            'nullable'  => true,
            'unique'    => $unique,
            'precision' => $precision,
            'scale'     => $scale,
        ));
    }

    public function getFormOptionsType()
    {
        return 'ww_field_type_decimal';
    }
}
