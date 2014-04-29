<?php

namespace Pum\Bundle\CoreBundle\Security\Authorization\Voter;

use Pum\Bundle\AppBundle\Entity\Permission;
use Pum\Core\ObjectFactory;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;

class ObjectVoter implements VoterInterface
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
        if (!in_array($attribute, Permission::$objectPermissions)) {
            return false;
        }

        return true;
    }

    public function supportsClass($class)
    {
        return 0 === strpos($class, 'pum_obj_');
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (is_object($object) && !$this->supportsClass(get_class($object))) {
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

        list($project, $object) = $this->objectFactory->getProjectAndObjectFromClass(get_class($object));
        $beam = $object->getBeam();

        foreach ($user->getGroups() as $group) {
            foreach ($group->getAdvancedPermissions() as $permission) {
                if ($attribute == $permission->getAttribute()
                    && $project == $permission->getProject()
                    && $beam == $permission->getBeam()
                    && $object == $permission->getObject()
                    && null == $permission->getInstance()
                ) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
