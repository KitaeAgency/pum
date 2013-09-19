<?php

namespace Pum\Core;

interface TypeExtensionInterface
{
    public function setDefaultOptions(OptionsResolverInterface $resolver);
    public function buildField(FieldBuildContext $context);
    public function getExtendedType();
}
