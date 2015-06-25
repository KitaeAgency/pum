<?php

namespace Pum\Core\Extension\Security\Behavior;

use Pum\Core\Behavior;
use Pum\Core\BehaviorInterface;
use Pum\Core\Context\ObjectBuildContext;
use Pum\Core\Context\ObjectContext;
use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\Extension\Util\Namer;
use Symfony\Component\Form\FormBuilderInterface;

class SecurityUserBehavior extends Behavior
{
    const ROLE_FIELD_NAME     = 'object_role';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            $builder->create('security_user', 'section')
            ->add('user_security', 'ww_object_definition_security_user', array(
                'label' => ' ',
                'attr' => array(
                    'class' => 'pum-scheme-panel-carrot'
                ),
                'objectDefinition' => $builder->getData()
            ))
        );
    }

    public function getProjectAdminForm()
    {
        return 'pum_object_security';
    }

    public static function getCamelCaseRoleField()
    {
        return Namer::toCamelCase(self::ROLE_FIELD_NAME);
    }

    public function mapDoctrineObject(ObjectContext $context, ClassMetadata $metadata)
    {
        $metadata->mapField(array(
            'columnName' => Namer::toLowercase(self::ROLE_FIELD_NAME),
            'fieldName' => Namer::toCamelCase(self::ROLE_FIELD_NAME),
            'type'      => 'simple_array',
            'nullable'  => true
        ));
    }

    public function buildObject(ObjectBuildContext $context)
    {
        $cb = $context->getClassBuilder();
        $usernameField = $context->getObject()->getSecurityUsernameField();
        $passwordField = $context->getObject()->getSecurityPasswordField();
        $roleField = Namer::toCamelCase(self::ROLE_FIELD_NAME);

        if (!$usernameField || !$passwordField) {
            return; // misconfigured
        }

        $cb->addImplements('Pum\Core\Extension\Security\PumUserInterface');

        $cb->createProperty($roleField);
        $cb->addGetMethod($roleField);
        $cb->addSetMethod($roleField);

        $cb->createMethod('eraseCredentials', '', '');
        $cb->createMethod('getRoles', null, '
            if ($this->get'.ucfirst($roleField).'() && is_array($this->get'.ucfirst($roleField).'())) {
                return array_merge(array("ROLE_USER"), $this->get'.ucfirst($roleField).'());
            }

            return array("ROLE_USER");
        ');
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
