<?php

namespace Pum\Bundle\CoreBundle\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;

class ProjectVoter implements VoterInterface
{
    private static $supportedAttributes = array(
        'PUM_PROJECT_LIST',
        'PUM_PROJECT_CREATE',
        'PUM_PROJECT_VIEW',
        'PUM_PROJECT_EDIT',
        'PUM_PROJECT_DELETE',
    );

    public function supportsAttribute($attribute)
    {
        if (!in_array($attribute, self::$supportedAttributes)) {
            return false;
        }

        return true;
    }

    public function supportsClass($class)
    {
        $supportedClass = 'Pum\Core\Definition\Project';

        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }

    public function vote(TokenInterface $token, $project, array $attributes)
    {
        if (!$this->supportsClass(get_class($project))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if(1 !== count($attributes)) {
            throw new InvalidArgumentException('Only one attribute is allowed');
        }

        $attribute = $attributes[0];

        /** @var \Pum\Bundle\AppBundle\Entity\User $user */
        $user = $token->getUser();

        if (!$this->supportsAttribute($attribute)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_DENIED;
        }

        if (!$user->hasProjectPermission($attribute, $project)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }

}
