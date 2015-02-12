<?php

namespace Pum\Bundle\CoreBundle\Security\Authorization\Voter;

use Pum\Bundle\AppBundle\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;

class ObjectVoter implements VoterInterface
{
    public function supportsAttribute($attribute)
    {
        if (!in_array($attribute, Permission::$objectPermissions)) {
            return false;
        }

        return true;
    }

    public function supportsClass($class)
    {
        return true;
    }

    public function vote(TokenInterface $token, $array, array $attributes)
    {
        if (!is_array($array) || !isset($array['project'])) {
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

        $project = $array['project'];
        $beam    = isset($array['beam']) ? $array['beam'] : null;
        $object  = isset($array['object']) ? $array['object'] : null;
        $id      = isset($array['id']) ? $array['id'] : null;

        // if a user can manage projects in WoodWork, he can manage anything
        if (in_array('ROLE_WW_PROJECTS', $user->getRoles())) {
            return self::ACCESS_GRANTED;
        }

        if (($group = $user->getGroup())) {
            foreach ($group->getAdvancedPermissions() as $permission) {

                $hasMasterPermission = $permission->getAttribute() == 'PUM_OBJ_MASTER';
                $hasViewPermission = in_array($permission->getAttribute(), array('PUM_OBJ_VIEW', 'PUM_OBJ_EDIT'));
                $isViewVote = $attribute == 'PUM_OBJ_VIEW';

                $attributeMatch = $attribute == $permission->getAttribute() || ($isViewVote && $hasViewPermission);

                //Denied quickly if the attribute is not matching
                if (!$hasMasterPermission && !$attributeMatch) {
                    continue;
                }

                //Project
                if ($beam == null && $object == null && $id == null) {
                    //Has permission at project level
                    if ($project == $permission->getProjectName()
                    ) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                }

                //Beam
                if ($beam && $object == null && $id == null) {
                    //Has permission at beam level
                    if ($project == $permission->getProjectName()
                        && $beam == $permission->getBeamName()
                    ) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                }

                //Object
                if ($beam && $object && $id == null) {
                    //Has permission at object level
                    if ($project == $permission->getProjectName()
                        && $beam == $permission->getBeamName()
                        && $object == $permission->getObjectName()
                    ) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                }

                //Instance
                if ($beam && $object && $id) {
                    //Has permission at instance level
                    if ($project == $permission->getProjectName()
                        && $beam == $permission->getBeamName()
                        && $object == $permission->getObjectName()
                        && $id == $permission->getInstance()
                    ) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                }

                //Has permission at project level
                if ($project == $permission->getProjectName()
                    && null == $permission->getBeamName()
                    && null == $permission->getObjectName()
                    && null == $permission->getInstance()
                ) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                //Has permission at beam level
                if ($project == $permission->getProjectName()
                    && $beam == $permission->getBeamName()
                    && null == $permission->getObjectName()
                    && null == $permission->getInstance()
                ) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                //Has permission at object level
                if ($project == $permission->getProjectName()
                    && $beam == $permission->getBeamName()
                    && $object == $permission->getObjectName()
                    && null == $permission->getInstance()
                ) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                //Has permission at instance level
                if ($project == $permission->getProjectName()
                    && $beam == $permission->getBeamName()
                    && $object == $permission->getObjectName()
                    && $id == $permission->getInstance()
                ) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
