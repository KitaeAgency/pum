<?php

namespace Pum\Core\Extension\Core\Form\Type;

use Pum\Core\Definition\View\FormView;
use Pum\Core\Extension\Util\Namer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView as FormFormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class PasswordType extends AbstractType
{
    private $encoderFactory;

    public function __construct(EncoderFactory $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['repeated']) {
            $this->buildFormRepeated($builder, $options);
        } else {
            $this->buildFormSimple($builder, $options);
        }
    }

    public function buildView(FormFormView $view, FormInterface $form, array $options)
    {
        $view->vars['repeated'] = $options['repeated'];
    }

    public function buildFormSimple(FormBuilderInterface $builder, array $options)
    {
        $ef = $this->encoderFactory;

        $builder
            ->add('single', 'password')
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($ef) {
                $form = $event->getForm();

                $passwords = $event->getData();

                // empty password
                if (!$passwords['single']) {
                    return;
                }

                $root = $form->getParent()->getData();
                $name = $form->getName();
                $method = 'set'.ucfirst(Namer::toCamelCase($name));

                if (!method_exists($root, $method)) {
                    return;
                }

                $root->$method($passwords['single'], $ef);

            })
        ;
    }

    public function buildFormRepeated(FormBuilderInterface $builder, array $options)
    {
        $ef = $this->encoderFactory;

        $builder
            ->add('first', 'password')
            ->add('second', 'password')
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($ef) {
                $form = $event->getForm();

                $passwords = $event->getData();

                // not matching
                if ($passwords['first'] !== $passwords['second']) {
                    $form->addError(new FormError('Passwords don\'t match'));

                    return;
                }

                // empty password
                if (!$passwords['first']) {
                    return;
                }

                $root = $form->getParent()->getData();
                $name = $form->getName();
                $method = 'set'.ucfirst(Namer::toCamelCase($name));

                if (!method_exists($root, $method)) {
                    return;
                }

                $root->$method($passwords['first'], $ef);

            })
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'mapped' => false,
            'repeated' => false,
            'first_options' => array(),
            'second_options' => array()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pum_password';
    }
}
