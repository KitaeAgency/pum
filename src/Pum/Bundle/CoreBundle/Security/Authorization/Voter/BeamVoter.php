<?php

namespace Pum\Bundle\CoreBundle\Security\Authorization\Voter;

use Pum\Bundle\CoreBundle\PumContext;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;

class BeamVoter implements VoterInterface
{
    /**
     * @var PumContext
     */
    private $pumContext;

    private static $supportedAttributes = array(
        'PUM_BEAM_LIST',
        'PUM_BEAM_CREATE',
        'PUM_BEAM_VIEW',
        'PUM_BEAM_EDIT',
        'PUM_BEAM_DELETE',
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
        $supportedClass = 'Pum\Core\Definition\Beam';

        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }

    public function vote(TokenInterface $token, $beam, array $attributes)
    {
        if (!$this->supportsClass(get_class($beam))) {
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

        if ('PUM_BEAM_CREATE' == $attribute) {
            $beam = null;
        }

        if (!$user->hasPermission($attribute, $project, $beam)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }
}
