<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Bundle\AppBundle\Entity\Permission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class CustomViewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (null === $project = $builder->getForm()->getData()->getProject()) {
            throw new \RuntimeException('You must set a project to the custom view');
        }

        $builder
            ->add('projectname', 'text', array(
                    'read_only' => true,
                    'data'     => ucfirst($project->getName()),
                    'mapped'   => false,
                ))
            ->add('beam', 'entity', array(
                    'class'       => 'Pum\Core\Definition\Beam',
                    'choices'     => array(),
                    'property'    => 'name',
                    'empty_value' => true,
                    'required'    => true,
                ))
            ->add('object', 'entity', array(
                    'class'       => 'Pum\Core\Definition\ObjectDefinition',
                    'choices'     => array(),
                    'property'    => 'name',
                    'empty_value' => true,
                    'required'    => true,
                ))
            ->add('tableView', 'entity', array(
                    'class'       => 'Pum\Core\Definition\View\TableView',
                    'choices'     => array(),
                    'property'    => 'name',
                    'empty_value' => true,
                    'required'    => true,
                ))
            ->add('save', 'submit')
        ;

        $formModifier = function (FormInterface $form, $project = null, $beam = null, $object = null) {
            if ($project) {
                $form->add('beam', 'entity', array(
                    'class'       => 'Pum\Core\Definition\Beam',
                    'choices'     => $project->getBeams(),
                    'property'    => 'name',
                    'empty_value' => true,
                    'required'    => true,
                ));
            }
            if ($beam) {
                $form->add('object', 'entity', array(
                    'class'       => 'Pum\Core\Definition\ObjectDefinition',
                    'property'    => 'name',
                    'empty_value' => true,
                    'required'    => true,
                    'query_builder' => function(EntityRepository $er) use ($beam) {
                        return $er->createQueryBuilder('e')
                            ->leftJoin('e.tableViews', 'tv')
                            ->andWhere('tv IS NOT NULL')
                            ->andWhere('e.beam = :beam')
                            ->setParameter('beam', $beam)
                            ->orderBy('e.name', 'ASC');
                    },
                ));
            }
            if ($object) {
                $form->add('tableView', 'entity', array(
                    'class'       => 'Pum\Core\Definition\View\TableView',
                    'property'    => 'name',
                    'empty_value' => true,
                    'required'    => true,
                    'query_builder' => function(EntityRepository $er) use ($object) {
                        return $er->createQueryBuilder('e')
                            ->andWhere('e.objectDefinition = :objectDefinition')
                            ->setParameter('objectDefinition', $object)
                            ->orderBy('e.name', 'ASC');
                    },
                ));
            }
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            $data = $event->getData();
            $formModifier($event->getForm(), $data->getProject(), $data->getBeam(), $data->getObject());
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($formModifier) {
            $form = $event->getForm();
            $data = $event->getData();

            $project     = $data->getProject();
            $beamId      = $form->get('beam')->getViewData();
            $objectId    = $form->get('object')->getViewData();
            $tableViewId = $form->get('tableView')->getViewData();

            $beam = $object = $view = null;

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

            if ($object && $tableViewId) {
                $viewsArray = $object->getTableViews()->toArray();
                $view = array_filter($viewsArray, function($view) use($tableViewId) {
                        if ($view->getId() == $tableViewId) {
                            return $view;
                        }
                    });
                $view = reset($view);
                $data->setTableView($view);
            }

            $formModifier($event->getForm(), $project, $beam, $object);
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => 'Pum\Bundle\ProjectAdminBundle\Entity\CustomView',
            'translation_domain' => 'pum_form',
            'type'               => 'user'
        ));
    }

    public function getName()
    {
        return 'pa_custom_view';
    }
}
