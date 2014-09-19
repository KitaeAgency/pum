<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\Type;

use Pum\Core\Vars\VarsInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VarType extends AbstractType
{
    protected $vars;

    public function __construct(VarsInterface $vars)
    {
        $this->vars = $vars;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA , function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            $typeValue = 'text';
            if (null !== $data && isset($data['type'])) {
                switch ($data['type']) {
                    case 'integer':
                        $typeValue = 'number';
                        break;

                    case 'boolean':
                        $typeValue = 'checkbox';
                        break;
                }
            }

            $keyReadOnly = false;
            if (null !== $data && isset($data['key'])) {
                $keyReadOnly = true;
            }

            $form
                ->add('key', 'text', array('read_only' => $keyReadOnly))
                ->add('value', $typeValue)
                ->add('type', 'choice', array(
                    'choices' => array('string' => 'string', 'integer' => 'integer', 'boolean' => 'boolean'),
                ))
                ->add('description', 'textarea', array('required' => false))
                ->add('save', 'submit')
            ;
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $this->vars->save($data);
        });
    }


    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'pum_form'
        ));
    }

    public function getName()
    {
        return 'pum_var';
    }
}
