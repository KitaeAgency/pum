<?php

namespace Pum\Core\Extension\Validation\Validation\Loader;

use Pum\Core\ObjectFactory;
use Pum\Core\Exception\TypeNotFoundException;
use Pum\Core\Context\FieldContext;
use Pum\Core\Extension\EmFactory\EmFactoryFeatureInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Loader\LoaderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PumLoader implements LoaderInterface
{
    protected $factory;
    
    public function __construct(ObjectFactory $factory)
    {
        $this->factory = $factory;
    }

    public function loadClassMetadata(ClassMetadata $metadata)
    {
        $className = $metadata->getClassname();

        try {
            list($project, $object) = $this->factory->getProjectAndObjectFromClass($className);

            foreach ($object->getFields() as $field) {
                try {
                    $types = $this->factory->getTypeHierarchy($field->getType());
                } catch (TypeNotFoundException $e) {
                    $project->addContextError(sprintf(
                        'Field "%s": type "%s" does not exist.',
                        $object->getName().'::'.$field->getName(),
                        $field->getType()
                    ));

                    continue;
                }
                $options = $field->getTypeOptions();

                $resolver = new OptionsResolver();
                foreach ($types as $type) {
                    $type->setDefaultOptions($resolver);
                }
                $options = $resolver->resolve($options);

                $context = new FieldContext($project, $field, $options);
                $context->setObjectFactory($this->factory);

                foreach ($types as $type) {
                    if ($type instanceof EmFactoryFeatureInterface) {
                        $type->mapValidation($context, $metadata);
                    }
                }
            }
        } catch (\InvalidArgumentException $e) {}
    }
}
