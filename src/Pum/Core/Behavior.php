<?php

namespace Pum\Core;

use Pum\Core\Context\ObjectContext;
use Pum\Core\Context\ObjectBuildContext;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

abstract class Behavior implements BehaviorInterface
{
    public function mapDoctrineObject(ObjectContext $context, ClassMetadata $metadata)
    {
        return;
    }

    public function buildObject(ObjectBuildContext $context)
    {
        return;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return;
    }
}
