<?php

namespace Pum\Core;

interface TypeInterface
{
    public function setDefaultOptions(OptionsResolverInterface $resolver);
    public function buildField(FieldBuildContext $context);
    public function getName();
    public function getParent();
}
