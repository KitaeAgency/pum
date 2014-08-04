<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\View\ObjectViewField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ObjectViewType extends AbstractType
{
    private $securityContext;

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $objectView = $builder->getData();

        switch ($options['form_type']) {
            case 'name':
                $builder
                    ->add($builder->create('objectview', 'section')
                        ->add('name', 'text')
                        ->add('private', 'checkbox', array(
                            'required'  =>  false
                        ))
                        ->add('create_default', 'checkbox', array(
                            'required'  =>  false,
                            'data'      =>  true,
                            'mapped'    =>  false
                        ))
                    )
                    ->add('save', 'submit')
                ;

                $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                    $data = $event->getData();

                    if (isset($data['objectview']['create_default']) && $data['objectview']['create_default']) {
                        $objectView = $event->getForm()->getData();

                        $i = 1;
                        foreach ($objectView->getObjectDefinition()->getFields() as $field) {
                            $objectView->createField($field->getName(), $field, ObjectViewField::DEFAULT_VIEW, $i++);
                        }
                    }
                });
            break;

            case 'full':
                $objectviewSection = $builder->create('objectview', 'section');
                $objectviewSection
                    ->add('name', 'text')
                    ->add('private', 'checkbox', array(
                        'required'  => false
                    ))
                ;

                if (true === $this->securityContext->isGranted('ROLE_PA_DEFAULT_VIEWS')) {
                    $objectviewSection
                        ->add('is_default', 'checkbox', array(
                            'data'      => $builder->getForm()->getData() === $builder->getForm()->getData()->getObjectDefinition()->getDefaultObjectView(),
                            'required'  => false,
                            'mapped'    => false
                        ))
                    ;

                    $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                        $data             = $event->getData();
                        $objectView       = $event->getForm()->getData();
                        $objectDefinition = $objectView->getObjectDefinition();

                        if (isset($data['objectview']['is_default']) && $data['objectview']['is_default']) {
                            $objectDefinition->setDefaultObjectView($objectView);
                        } elseif ($objectDefinition->getDefaultObjectView() === $objectView) {
                            $objectDefinition->setDefaultObjectView(null);
                        }
                    });
                }

                $builder
                    ->add($objectviewSection)
                    ->add('fields', 'pa_objectview_field_collection', array(
                        'options' => array(
                            'required'      =>  false,
                            'object_view'   =>  $objectView
                        )
                    ))
                    ->add('save', 'submit')
                ;
            break;
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'            => 'Pum\Core\Definition\View\ObjectView',
            'form_type'             => 'name',
            'translation_domain'    => 'pum_form'
        ));
    }

    public function getName()
    {
        return 'pa_objectview';
    }
}
