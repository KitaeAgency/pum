<?php

namespace Pum\Core\Extension\Tree\Behavior;

use Pum\Core\Behavior;
use Pum\Core\BehaviorInterface;
use Pum\Core\Context\ObjectBuildContext;
use Pum\Core\Context\ObjectContext;
use Pum\Core\Extension\Util\Namer;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Form\FormBuilderInterface;

class TreeBehavior extends Behavior
{
    const SEQUENCE_FIELD = 'tree_sequence';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            $builder->create('tree', 'section')
            ->add('tree', 'ww_object_definition_tree', array(
                'label' => ' ',
                'attr' => array(
                    'class' => 'pum-scheme-panel-darkgrass'
                ),
                'objectDefinition' => $builder->getData()
            ))
        );
    }

    public function mapDoctrineObject(ObjectContext $context, ClassMetadata $metadata)
    {
        $metadata->mapField(array(
            'columnName' => Namer::toLowercase(self::SEQUENCE_FIELD),
            'fieldName'  => Namer::toCamelCase(self::SEQUENCE_FIELD),
            'type'       => 'integer',
            'nullable'   => true,
        ));
    }

    public function buildObject(ObjectBuildContext $context)
    {
        $cb = $context->getClassBuilder();
        $field = $context->getObject()->isTreeEnabled();
        if (!$field) {
            return; // misconfigured
        }

        $sequenceField = Namer::toCamelCase(self::SEQUENCE_FIELD);

        $cb->addImplements('Pum\Core\Extension\Tree\TreeableInterface');

        $cb->createProperty($sequenceField);
        $cb->addGetMethod($sequenceField);
        $cb->addSetMethod($sequenceField);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tree';
    }
}
