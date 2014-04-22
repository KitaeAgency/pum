<?php

namespace Pum\Bundle\CoreBundle\Security\Authorization\Voter;

use Pum\Bundle\CoreBundle\PumContext;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;

class ObjectVoter implements VoterInterface
{
    /**
     * @var PumContext
     */
    private $pumContext;

    private static $supportedAttributes = array(
        'PUM_OBJECT_LIST',
        'PUM_OBJECT_CREATE',
        'PUM_OBJECT_VIEW',
        'PUM_OBJECT_EDIT',
        'PUM_OBJECT_DELETE',
    );

    public function __construct(PumContext $pumContext)
    {
        $this->pumContext   = $pumContext;
    }

    public function supportsAttribute($attribute)
    {
        if (!in_array($attribute, self::$supportedAttributes)) {
            return false;
        }

        return true;
    }

    public function supportsClass($class)
    {
        $supportedClass = 'Pum\Core\Definition\ObjectDefinition';

        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$this->supportsClass(get_class($object))) {
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

        $project = $this->pumContext->getProject();
        $beam = $object->getBeam();

        if (!$user->hasPermission($attribute, $project, $beam, $object)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }
}
