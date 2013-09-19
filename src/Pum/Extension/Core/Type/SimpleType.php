<?php

namespace Pum\Core;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SimpleType implements TypeInterface
{
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
            'required' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildField(FieldBuildContext $context)
    {
        $camelCase = $context->getFieldCamelCase();
        $cb = $context->getClassBuilder();
        $cb->addProperty($camelCase, Property::VISIBILITY_PROTECTED);
        $cb->addMethod('get'.ucfirst($camelCase), '', 'return $this->'.$camelCase.';');
        $cb->addMethod('set'.ucfirst($camelCase), '$'.$camelCase, '$this->'.$camelCase.' = $ '.$camelCase.'; return $this;');
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return null;
    }
}
