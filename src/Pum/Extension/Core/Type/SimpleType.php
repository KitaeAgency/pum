<?php

namespace Pum\Extension\Core\Type;

use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SimpleType extends AbstractType
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

        $cb->getMethod('getName')->prependCode('$name = strtoupper($name);');
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return null;
    }
}
