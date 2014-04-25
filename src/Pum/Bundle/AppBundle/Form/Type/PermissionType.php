<?php

namespace Pum\Bundle\AppBundle\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Pum\Bundle\AppBundle\Entity\Permission;
use Pum\Bundle\CoreBundle\PumContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class PermissionType extends AbstractType
{
    /**
     * @var PumContext
     */
    private $pumContext;

    public function __construct(PumContext $pumContext)
    {
        $this->pumContext = $pumContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $permissions = array_merge(
            Permission::$projectPermissions,
            Permission::$beamPermissions,
            Permission::$objectPermissions
        );

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
                    'empty_value' => 'All projects',
                    'required' => false,
                ))
            ->add('beam', 'entity', array(
                    'class' => 'Pum\Core\Definition\Beam',
                    'property' => 'name',
                    'empty_value' => 'All beams',
                    'required' => false,
                ))
            ->add('object', 'entity', array(
                    'class' => 'Pum\Core\Definition\ObjectDefinition',
                    'property' => 'name',
                    'empty_value' => 'All objects',
                    'required' => false,
                ))
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
                if ($data = $event->getData()) {
                    $beam = null;
                    if ($data->getBeam()) {
                        $beam = $data->getBeam();
                    }
                    $formModifier($event->getForm(), $data->getProject()?:null, $beam);

                    //$data->setBeam($data->getBeam());
                    //$event->setData($data);
                }
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

        $builder->add('save', 'submit');
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
