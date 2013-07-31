<?php

namespace Pum\Bundle\CoreBundle\Validator\Constraints;

use Doctrine\Common\Collections\Collection;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\Project;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NoBeamTwiceValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Collection) {
            throw new UnexpectedTypeException($value, 'Doctrine\Common\Collections\Collection');
        }

        $names = array();
        foreach ($value as $key => $beam) {
            if (!$beam instanceof Beam) {
                throw new UnexpectedTypeException($value, 'Pum\Core\Definition\Beam');
            }

            $name = $beam->getName();
            if (isset($names[$name])) {
                $this->context->addViolationAt($key, strtr($constraint->message, array('%name%' => $name)));
            }

            $names[$name] = true;
        }
    }
}
