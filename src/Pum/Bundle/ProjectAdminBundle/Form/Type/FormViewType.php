<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Definition\View\FormViewField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class FormViewType extends AbstractType
{
    private $securityContext;

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formView = $builder->getData();

        switch ($options['form_type']) {
            case 'name':
                $builder
                    ->add($builder->create('formview', 'section')
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

                    if (isset($data['formview']['create_default']) && $data['formview']['create_default']) {
                        $formView = $event->getForm()->getData();

                        $i = 1;
                        foreach ($formView->getObjectDefinition()->getFields() as $field) {
                            $formView->createField($field->getName(), $field, FormViewField::DEFAULT_VIEW, $i++);
                        }
                    }
                });
            break;

            case 'full':
                $formviewSection = $builder->create('formview', 'section');
                $formviewSection
                    ->add('name', 'text')
                    ->add('private', 'checkbox', array(
                        'required'  => false
                    ))
                ;

                if (true === $this->securityContext->isGranted('ROLE_PA_DEFAULT_VIEWS')) {
                    $formviewSection
                        ->add('is_default', 'checkbox', array(
                            'data'      => $builder->getForm()->getData() === $builder->getForm()->getData()->getObjectDefinition()->getDefaultFormView(),
                            'required'  => false,
                            'mapped'    => false
                        ))
                    ;

                    $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                        $data             = $event->getData();
                        $formView         = $event->getForm()->getData();
                        $objectDefinition = $formView->getObjectDefinition();

                        if (isset($data['formview']['is_default']) && $data['formview']['is_default']) {
                            $objectDefinition->setDefaultFormView($formView);
                        } elseif ($objectDefinition->getDefaultFormView() === $formView) {
                            $objectDefinition->setDefaultFormView(null);
                        }
                    });
                }

                $builder
                    ->add($formviewSection)
                    ->add('fields', 'pa_formview_field_collection', array(
                            'options' => array(
                                'required'  =>  false,
                                'form_view' =>  $formView
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
            'data_class'         => 'Pum\Core\Definition\View\FormView',
            'form_type'          => 'name',
            'translation_domain' => 'pum_form'
        ));
    }

    public function getName()
    {
        return 'pa_formview';
    }
}
