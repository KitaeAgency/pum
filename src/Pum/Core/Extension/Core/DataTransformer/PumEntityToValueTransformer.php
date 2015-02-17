<?php

namespace Pum\Core\Extension\Core\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;

class PumEntityToValueTransformer implements DataTransformerInterface
{
    public function transform($choices)
    {
        if ($choices == '' || $choices === null) {
            return;
        }

        return $choices->getId();
    }

    public function reverseTransform($value)
    {
        if ('' === $value || null === $value) {
            return;
        }

        return $value;
    }
}