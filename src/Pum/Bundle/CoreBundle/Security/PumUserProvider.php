<?php

namespace Pum\Bundle\CoreBundle\Security;

use Pum\Bundle\CoreBundle\PumContext;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class PumUserProvider implements UserProviderInterface
{
    private $context;

    public function __construct(PumContext $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $project = $this->context->getProject();
        if (!$project) {
            throw new UsernameNotFoundException('Cannot load user: no project set in context');
        }

        $userObject = null;
        foreach ($project->getObjects() as $object) {
            if ($object->isSecurityUserEnabled()) {
                $userObject = $object;
            }
        }

        if (null === $userObject) {
            throw new UsernameNotFoundException(sprintf('No security user found in project "%s".', $project->getName()));
        }

        $oem = $this->context->getProjectOEM();

        $repo = $oem->getRepository($userObject->getName());
        $usernameField = $userObject->getSecurityUsernameField()->getCamelCaseName();

        $user = $repo->findOneBy(array(
            $usernameField => $username
        ));

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('User with username "%s" was not found in object "%s" (project: %s, field: %).', $username, $userObject->getName(), $project->getName(), $usernameField));
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return true;
    }
}
