<?php

namespace Pum\Bundle\AppBundle\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Bundle\AppBundle\Entity\Permission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PermissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $permissions = array_merge(Permission::$objectPermissions);
        $permissions = array_combine($permissions, $permissions);

        $builder
            ->add('group', 'entity', array(
                    'class' => 'Pum\Bundle\AppBundle\Entity\Group',
                    'property' => 'name',
                    'expanded' => true,
                ))
            ->add('attribute', 'choice', array(
                    'choices'  => $permissions,
                ))
            ->add('project', 'entity', array(
                    'class' => 'Pum\Core\Definition\Project',
                    'property' => 'name',
                    'empty_value' => '-- Select project --',
                ))
            ->add('beam', 'entity', array(
                    'class' => 'Pum\Core\Definition\Beam',
                    'choices' => array(),
                    'property' => 'name',
                    'empty_value' => 'All beams',
                    'required' => false,
                ))
            ->add('object', 'entity', array(
                    'class' => 'Pum\Core\Definition\ObjectDefinition',
                    'choices' => array(),
                    'property' => 'name',
                    'empty_value' => 'All objects',
                    'required' => false,
                ))
            ->add('instance', 'number', array(
                    'required' => false,
                    'label' => 'Instance (optional)',
                    'attr' => array(
                        'placeholder' => 'Object Definition ID',
                    )
                ))
            ->add('save', 'submit')
        ;

        $formModifier = function (FormInterface $form, $project = null, $beam = null) {
            if ($project) {
                $form->add('beam', 'entity', array(
                    'class' => 'Pum\Core\Definition\Beam',
                    'choices' => $project->getBeams(),
                    'property' => 'name',
                    'empty_value' => 'All beams',
                    'required' => false,
                ));
            }
            if ($beam) {
                $form->add('object', 'entity', array(
                    'class' => 'Pum\Core\Definition\ObjectDefinition',
                    'choices' => $beam->getObjects(),
                    'property' => 'name',
                    'empty_value' => 'All objects',
                    'required' => false,
                ));
            }
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();
                $formModifier($event->getForm(), $data->getProject(), $data->getBeam());
            }
        );

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $form = $event->getForm();
                $data = $event->getData();

                $project = $data->getProject()?:null;

                $beamId = $form->get('beam')->getViewData();
                $objectId = $form->get('object')->getViewData();

                $beam = null;

                if ($beamId) {
                    $beamsArray = $project->getBeams()->toArray();
                    $beam = array_filter($beamsArray, function($beam) use($beamId) {
                            if ($beam->getId() == $beamId) {
                                return $beam;
                            }
                        });
                    $beam = reset($beam);
                    $data->setBeam($beam);
                }

                if ($beam && $objectId) {
                    $objectsArray = $beam->getObjects()->toArray();
                    $object = array_filter($objectsArray, function($object) use($objectId) {
                            if ($object->getId() == $objectId) {
                                return $object;
                            }
                        });
                    $object = reset($object);
                    $data->setObject($object);
                }

                $formModifier($event->getForm(), $project, $beam);
            }
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'        => 'Pum\Bundle\AppBundle\Entity\Permission',
            'translation_domain' => 'pum_form'
        ));
    }

    public function getName()
    {
        return 'pum_permission';
    }
}
