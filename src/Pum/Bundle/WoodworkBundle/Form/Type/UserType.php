<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Bundle\WoodworkBundle\Entity\User;
use Pum\Core\Definition\Relation;
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
            ->add('username', 'text')
            ->add('fullname', 'text')
            ->add('password', 'repeated', array(
                'mapped' => false,
                'type'   => 'password',
                'constraints' => $passwordConstraints,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat password')
            ))
            ->add('groups', 'entity', array(
                'class' => 'Pum\Bundle\WoodworkBundle\Entity\Group',
                'property' => 'name',
                'expanded' => true,
                'multiple' => true
            ))
            ->add('save', 'submit')
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($factory) {
                $form = $event->getForm();
                $user = $form->getData();


                if (!$user instanceof User) {
                    return;
                }

                $user->setPassword($form->get('password')->getData(), $factory);

            })
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'        => 'Pum\Bundle\WoodworkBundle\Entity\User',
            'password_required' => true,
        ));
    }

    public function getName()
    {
        return 'ww_user';
    }
}
