<?php

namespace Pum\Bundle\CoreBundle\Security\Authorization\Voter;

use Pum\Bundle\AppBundle\Entity\Permission;
use Pum\Bundle\CoreBundle\PumContext;
use Pum\Core\ObjectFactory;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;

class BeamVoter implements VoterInterface
{
    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var PumContext
     */
    private $pumContext;

    public function __construct(PumContext $pumContext, ObjectFactory $objectFactory)
    {
        $this->pumContext = $pumContext;
        $this->objectFactory = $objectFactory;
    }

    public function supportsAttribute($attribute)
    {
        if (!in_array($attribute, Permission::$beamPermissions)) {
            return false;
        }

        return true;
    }

    public function supportsClass($beamName)
    {
        return is_string($beamName);
    }

    public function vote(TokenInterface $token, $beamName, array $attributes)
    {
        if (!$this->supportsClass($beamName)) {
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

        //Backward compatible with ROLE_WW_BEAMS
        foreach ($user->getGroups() as $group) {
            if (in_array('ROLE_WW_BEAMS', $group->getPermissions())) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        $project = $this->pumContext->getProject();
        $beam = $this->objectFactory->getBeam($beamName);

        foreach ($user->getGroups() as $group) {
            foreach ($group->getAdvancedPermissions() as $permission) {
                if ($attribute == $permission->getAttribute()
                    && $project == $permission->getProject()
                    && $beam == $permission->getBeam()
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
