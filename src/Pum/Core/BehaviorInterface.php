<?php

namespace Pum\Core;

use Pum\Core\Context\ObjectContext;
use Pum\Core\Context\ObjectBuildContext;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Form\FormBuilderInterface;

interface BehaviorInterface
{
    /**
     * @return boolean
     */
    public function isEnabled();

    /**
     * @param  FormBuilderInterface $builder
     * @param  array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options);

    /**
     * @return string
     */
    public function getProjectAdminForm();

    /**
     * @param  ObjectContext $context
     * @param  ClassMetadata $metadata
     */
    public function mapDoctrineObject(ObjectContext $context, ClassMetadata $metadata);

    /**
     * @param  ObjectBuildContext $context
     */
    public function buildObject(ObjectBuildContext $context);

    /**
     * @return string
     */
    public function getName();
}
