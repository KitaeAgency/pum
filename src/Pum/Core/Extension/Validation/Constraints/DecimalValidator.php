<?php

namespace Pum\Core\Extension\Validation\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 *
 * @api
 */
class DecimalValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!preg_match('/^(\-?\d+(\.\d+)?)$/', $value)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
