<?php

namespace Pum\Core;

use Pum\Core\Context\ObjectContext;
use Pum\Core\Context\ObjectBuildContext;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Form\FormBuilderInterface;

interface BehaviorInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options);

    public function mapDoctrineObject(ObjectContext $context, ClassMetadata $metadata);

    public function buildObject(ObjectBuildContext $context);

    /**
     * @return string
     */
    public function getName();
}
