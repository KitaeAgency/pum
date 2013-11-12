<?php

namespace Pum\Core\Extension\Core\Form\Type;

use Pum\Core\Extension\Util\Namer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
        $ef = $this->encoderFactory;

        $builder
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($ef) {
                $form = $event->getForm();

                if (!$form->isValid()) {
                    return;
                }

                $password = $form->getData();

                if (!$password) {
                    return;
                }

                $root = $form->getParent()->getData();
                $name = $form->getName();
                $method = 'set'.ucfirst(Namer::toCamelCase($name));

                if (!method_exists($root, $method)) {
                    return;
                }

                $root->$method($password, $ef);

            })
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'mapped' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'password';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pum_password';
    }
}
