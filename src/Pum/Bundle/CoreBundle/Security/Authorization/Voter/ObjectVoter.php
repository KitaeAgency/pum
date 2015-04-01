<?php

namespace Pum\Bundle\CoreBundle\Security\Authorization\Voter;

use Pum\Bundle\AppBundle\Entity\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;

class ObjectVoter implements VoterInterface
{
    protected $permissionsCache;

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

        $inherit = 'ALL';
        $project = $array['project'];
        $beam    = isset($array['beam']) ? $array['beam'] : null;
        $object  = isset($array['object']) ? $array['object'] : null;
        $id      = isset($array['id']) ? $array['id'] : null;
        $group   = $user->getGroup();

        // if a user is superadmin, he can manage anything
        if ($group && $group->isAdmin()) {
            return self::ACCESS_GRANTED;
        }

        // if a user can manage projects in WoodWork, he can manage anything
        if (in_array('ROLE_WW_PROJECTS', $user->getRoles())) {
            return self::ACCESS_GRANTED;
        }

        if (null === $this->permissionsCache) {
            if ($group) {
                foreach ($group->getAdvancedPermissions() as $permission) {
                    if ($permission->getAttribute() === 'PUM_OBJ_MASTER') {
                        $attrs = array('PUM_OBJ_VIEW','PUM_OBJ_EDIT','PUM_OBJ_CREATE','PUM_OBJ_DELETE');
                    } elseif ($permission->getAttribute() === 'PUM_OBJ_EDIT') {
                        $attrs = array('PUM_OBJ_VIEW','PUM_OBJ_EDIT');
                    } else {
                        $attrs = array($permission->getAttribute());
                    }

                    foreach ($attrs as $attr) {
                        $this->setPermission($attr, $permission);
                    }
                 }
            }

            if (null === $this->permissionsCache) {
                $this->permissionsCache = array();
            }
        }

        if ($beam == null && $object == null && $id == null) {//Project
            if (isset($this->permissionsCache[$attribute][$project])) {
                return VoterInterface::ACCESS_GRANTED;
            }
        } elseif ($object == null && $id == null) {//Beam
            if (isset($this->permissionsCache[$attribute][$project][$beam])) {
                return VoterInterface::ACCESS_GRANTED;
            }
        } elseif ($id == null) {//Object
            if (isset($this->permissionsCache[$attribute][$project][$beam][$object])) {
                return VoterInterface::ACCESS_GRANTED;
            }
        } elseif (isset($this->permissionsCache[$attribute][$project][$beam][$object][$id])) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if (isset($this->permissionsCache[$attribute][$project]) && $inherit === $this->permissionsCache[$attribute][$project]) { //Has permission at project level
            return VoterInterface::ACCESS_GRANTED;
        } elseif (isset($this->permissionsCache[$attribute][$project][$beam]) && $inherit === $this->permissionsCache[$attribute][$project][$beam]) { //Has permission at beam level
            return VoterInterface::ACCESS_GRANTED;
        } elseif (isset($this->permissionsCache[$attribute][$project][$beam][$object]) && $inherit === $this->permissionsCache[$attribute][$project][$beam][$object]) { //Has permission at object level
            return VoterInterface::ACCESS_GRANTED;
        } elseif (isset($this->permissionsCache[$attribute][$project][$beam][$object][$id]) && $inherit === $this->permissionsCache[$attribute][$project][$beam][$object][$id]) { //Has permission at instance level
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;

        // Work but a little slow with a lot of permissions
        /*if (($group = $user->getGroup())) {
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

        return VoterInterface::ACCESS_DENIED;*/
    }

    private function setPermission($attr, $permission)
    {
        $inherit     = 'ALL';
        $projectName = $permission->getProjectName();
        $beamName    = $permission->getBeamName();
        $objectName  = $permission->getObjectName();
        $instance    = $permission->getInstance();

        if (null === $beamName && null === $objectName && null === $instance) {
            $this->permissionsCache[$attr][$projectName] = $inherit;
        } elseif (null === $objectName && null === $instance && (!isset($this->permissionsCache[$attr][$projectName]) || $inherit !== $this->permissionsCache[$attr][$projectName])) {
            $this->permissionsCache[$attr][$projectName][$beamName] = $inherit;
        } elseif (null === $instance && (!isset($this->permissionsCache[$attr][$projectName][$beamName]) || $inherit !== $this->permissionsCache[$attr][$projectName][$beamName])) {
            $this->permissionsCache[$attr][$projectName][$beamName][$objectName] = $inherit;
        } elseif (!isset($this->permissionsCache[$attr][$projectName][$beamName][$objectName]) || $inherit !== $this->permissionsCache[$attr][$projectName][$beamName][$objectName]) {
            $this->permissionsCache[$attr][$projectName][$beamName][$objectName][$instance] = $inherit;
        }
    }
}
