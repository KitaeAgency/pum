<?php

namespace Pum\Bundle\AppBundle\Form\Type;

use Pum\Bundle\AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    const MIN_PASSWORD = 3;

    /**
     * @var EncoderFactoryInterface
     */
    private $factory;

    public function __construct(EncoderFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $this->factory;

        $passwordConstraints = array(
            new Length(array('min' => self::MIN_PASSWORD))
        );

        if ($options['password_required']) {
            $passwordConstraints[] = new NotBlank();
        }

        $builder
            ->add('username', 'email')
            ->add('fullname', 'text')
            ->add('password', 'repeated', array(
                'mapped' => false,
                'type'   => 'password',
                'required'    => $options['password_required'],
                'constraints' => $passwordConstraints
            ))
            ->add('group', 'entity', array(
                'class' => 'Pum\Bundle\AppBundle\Entity\Group',
                'property' => 'alias',
                'expanded' => false,
                'multiple' => false
            ))
            ->add('save', 'submit')
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($factory) {
                $form = $event->getForm();
                $user = $form->getData();

                if (!$user instanceof User) {
                    return;
                }
                if (!$password = $form->get('password')->getData()) {
                    return;
                }
                if (!$form->isValid()) {
                    return;
                }

                $user->setPassword($password, $factory);
            })
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => 'Pum\Bundle\AppBundle\Entity\User',
            'password_required'  => false,
            'translation_domain' => 'pum_form'
        ));
    }

    public function getName()
    {
        return 'pum_user';
    }
}
