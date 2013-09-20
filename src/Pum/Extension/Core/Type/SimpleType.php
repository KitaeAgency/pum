<?php

namespace Pum\Extension\Core\Type;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineMetadata;
use Pum\Core\AbstractType;
use Pum\Core\ClassBuilder\Property;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Definition\FieldDefinition;
use Pum\Extension\EmFactory\EmFactoryFeatureInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SimpleType extends AbstractType implements EmFactoryFeatureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'simple';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'required'          => false,
            'doctrine_type'     => 'text',
            'doctrine_length'   => null,
            'doctrine_required' => function (Options $options) {
                return $options['required'];
            }
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildField(FieldBuildContext $context)
    {
        $camelCase = $context->getField()->getCamelCaseName();
        $cb = $context->getClassBuilder();

        $cb
            ->createProperty($camelCase)
            ->addGetMethod($camelCase)
            ->addSetMethod($camelCase)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineField(FieldDefinition $field, DoctrineMetadata $metadata)
    {
        die('@todo mapField simple');
    }
}
