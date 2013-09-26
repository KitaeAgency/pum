<?php

namespace Pum\Core;

use Pum\Core\Context\FieldBuildContext;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

interface TypeInterface
{
    public function setDefaultOptions(OptionsResolverInterface $resolver);
    public function buildField(FieldBuildContext $context);
    public function getName();
    public function getParent();
}
