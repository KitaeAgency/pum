<?php

namespace Pum\Core;

use Pum\Core\Context\FieldBuildContext;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

interface TypeExtensionInterface
{
    public function setDefaultOptions(OptionsResolverInterface $resolver);
    public function buildField(FieldBuildContext $context);
    public function getExtendedType();
}
