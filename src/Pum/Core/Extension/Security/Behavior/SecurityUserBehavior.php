<?php

namespace Pum\Core\Extension\Security\Behavior;

use Pum\Core\BehaviorInterface;
use Pum\Core\Context\ObjectBuildContext;
use Pum\Core\Context\ObjectContext;
use Doctrine\ORM\Mapping\ClassMetadata;

class SecurityUserBehavior implements BehaviorInterface
{
    public function mapDoctrineObject(ObjectContext $context, ClassMetadata $metadata)
    {
        return;
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
