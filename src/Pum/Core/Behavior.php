<?php

namespace Pum\Core;

use Pum\Core\Context\ObjectContext;
use Pum\Core\Context\ObjectBuildContext;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

abstract class Behavior implements BehaviorInterface
{
    const HAS_VIEW_TAB      = false;
    const HAS_EDIT_TAB      = false;

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * @param  ObjectContext $context
     * @param  ClassMetadata $metadata
     */
    public function mapDoctrineObject(ObjectContext $context, ClassMetadata $metadata)
    {
        return;
    }

    /**
     * @param  ObjectBuildContext $context
     */
    public function buildObject(ObjectBuildContext $context)
    {
        return;
    }

    /**
     * @param  FormBuilderInterface $builder
     * @param  array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return;
    }

    /**
     * @return string
     */
    public function getProjectAdminForm()
    {
        return null;
    }
}
