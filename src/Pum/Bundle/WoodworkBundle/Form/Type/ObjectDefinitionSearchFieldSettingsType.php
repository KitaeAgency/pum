<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObjectDefinitionSearchFieldSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'choice', array(
                'choices' => array('analyzer' => 'analyzer'),
                'placeholder' => true,
                'attr' => array('class' => 'form-ajax-reload')
            ))
        ;

        $formTypeModifier = function (FormInterface $form, $settings = array()) {
            if (isset($settings['type'])) {
                switch ($settings['type']) {
                    case 'analyzer':
                        $form->add('analyzer_name', 'text', array(
                            'required' => true
                        ));
                        $form->add('analyzer_type', 'choice', array(
                            'required' => true,
                            'choices' => array('standard' => 'standard')
                        ));
                        $form->add('analyzer_stopwords', 'checkbox', array(
                            'value' => true,
                            'required' => false,
                            'attr' => array('class' => 'form-ajax-reload')
                        ));

                        if (isset($settings['analyzer_stopwords']) && $settings['analyzer_stopwords'] == true) {
                            $form->add('analyzer_stopwords_list', 'text', array(
                                // This option should be set to true, but since we don't have a ajax reload yet, we don't want to lock the form
                                // if the previous checkbox is not checked.
                                'required' => false,
                            ));
                        }

                        break;
                }
            }
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formTypeModifier) {
                $settings = $event->getData();

                $formTypeModifier($event->getForm(), $settings);
            }
        );

        $builder->get('type')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formTypeModifier) {
                $settings = $event->getForm()->getData();

                $formTypeModifier($event->getForm()->getParent(), $settings);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ww_object_definition_search_field_settings';
    }
}
