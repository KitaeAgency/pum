<?php

namespace Pum\Core\Extension\Security\Behavior;

use Pum\Core\Behavior;
use Pum\Core\BehaviorInterface;
use Pum\Core\Context\ObjectBuildContext;
use Pum\Core\Context\ObjectContext;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Form\FormBuilderInterface;

class SecurityUserBehavior extends Behavior
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add($builder->create('security_user', 'section')
            ->add('user_security', 'ww_object_definition_security_user', array(
                'label' => ' ',
                'attr' => array(
                    'class' => 'pum-scheme-panel-carrot'
                ),
                'objectDefinition' => $builder->getData()
            ))
        );
    }

    public function buildObject(ObjectBuildContext $context)
    {
        $cb = $context->getClassBuilder();
        $usernameField = $context->getObject()->getSecurityUsernameField();
        $passwordField = $context->getObject()->getSecurityPasswordField();
        if (!$usernameField || !$passwordField) {
            return; // misconfigured
        }

        $cb->addImplements('Pum\Core\Extension\Security\PumUserInterface');

        $cb->createMethod('eraseCredentials', '', '');
        $cb->createMethod('getRoles', '', 'return array("ROLE_USER");');
        $cb->createMethod('getSalt', '', 'return $this->'.$passwordField->getCamelCaseName().'Salt;');

        if ($usernameField->getCamelCaseName() !== 'username') {
            $cb->createMethod('getUsername', '', 'return $this->'.$usernameField->getCamelCaseName().';');
        }

        if ($passwordField->getCamelCaseName() !== 'password') {
            $cb->createMethod('getPassword', '', 'return $this->'.$passwordField->getCamelCaseName().';');
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'security_user';
    }
}
