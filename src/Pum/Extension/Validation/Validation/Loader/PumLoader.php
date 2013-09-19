<?php

use Pum\Core\Object\ObjectFactory;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Loader\LoaderInterface;

class PumLoader implements LoaderInterface
{
    public function loadClassMetadata(ClassMetadata $metadata)
    {
        $className = $metadata->getClassname();

        if (0 !== strpos($className, ObjectFactory::CLASS_PREFIX)) {
            return;
        }

        $objectMetadata = $className::_pumGetMetadata();

        foreach ($objectMetadata->types as $name => $type) {
            $objectMetadata->getType($name)->mapValidation($metadata, $name, $objectMetadata->typeOptions[$name]);
        }
    }
}
