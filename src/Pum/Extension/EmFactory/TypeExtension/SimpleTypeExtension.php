<?php

namespace Pum\Extension\EmFactory\TypeExtension;

use Pum\Core\TypeExtensionInterface;
use Pum\Extension\EmFactory\EmFactoryFeatureInterface;
use Symfony\Component\OptionsResolver\Options;

class SimpleTypeExtension implements TypeExtensionInterface, EmFactoryFeatureInterface
{
    public function getExtendedType()
    {
        return 'scalar';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'doctrine_type'   => 'text',
            'doctrine_length' => null,
            'doctrine_required' => function (Options $options) {
                return $options['required'];
            }
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(FieldBuildContext $context, ObjectClassMetadata $metadata)
    {
    }
}
