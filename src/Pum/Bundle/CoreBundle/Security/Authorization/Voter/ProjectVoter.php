<?php

namespace Pum\Bundle\CoreBundle\Security\Authorization\Voter;

use Pum\Bundle\AppBundle\Entity\Permission;
use Pum\Core\ObjectFactory;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;

class ProjectVoter implements VoterInterface
{
    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    public function __construct(ObjectFactory $objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }

    public function supportsAttribute($attribute)
    {
        if (!in_array($attribute, Permission::$projectPermissions)) {
            return false;
        }

        return true;
    }

    public function supportsClass($projectName)
    {
        return is_string($projectName);
    }

    public function vote(TokenInterface $token, $projectName, array $attributes)
    {
        if (!$this->supportsClass($projectName)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if(1 !== count($attributes)) {
            throw new InvalidArgumentException('Only one attribute is allowed');
        }

        $attribute = $attributes[0];

        if (!$this->supportsAttribute($attribute)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_DENIED;
        }

        //Backward compatible with ROLE_WW_PROJECTS
        foreach ($user->getGroups() as $group) {
            if (in_array('ROLE_WW_PROJECTS', $group->getPermissions())) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        $project = $this->objectFactory->getProject($projectName);

        foreach ($user->getGroups() as $group) {
            foreach ($group->getAdvancedPermissions() as $permission) {
                if ($attribute == $permission->getAttribute()
                    && $project == $permission->getProject()
                    && null == $permission->getBeam()
                    && null == $permission->getObject()
                    && null == $permission->getInstance()
                ) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
