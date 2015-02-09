<?php

namespace Pum\Core\Extension\Core\DataTransformer;

use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;

class PumEntitiesToValueTransformer implements DataTransformerInterface
{

    public function transform($array)
    {
        if (null === $array) {
            return array();
        }

        $values = array();
        foreach ($array as $item) {
            $values[] = $item->getId();
        }

        return $values;
    }

    public function reverseTransform($array)
    {
        if (null === $array) {
            return array();
        }

        return $array;
    }
}
