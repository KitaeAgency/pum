<?php

namespace Pum\Core\Extension\Tree;

use Pum\Core\Behavior;
use Pum\Core\BehaviorInterface;
use Pum\Core\Context\ObjectBuildContext;
use Pum\Core\Context\ObjectContext;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Form\FormBuilderInterface;

class TreeBehavior extends Behavior
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add($builder->create('tree', 'section')
            ->add('tree', 'ww_object_definition_tree', array(
                'label' => ' ',
                'attr' => array(
                    'class' => 'pum-scheme-panel-darkgrass'
                ),
                'objectDefinition' => $builder->getData()
            ))
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tree';
    }
}
