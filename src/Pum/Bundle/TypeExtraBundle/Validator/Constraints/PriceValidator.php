<?php

namespace Pum\Bundle\TypeExtraBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 *
 * @api
 */
class PriceValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if ($constraint->allowNegativePrice) {
            $data = array(
                'pattern' => '/^-{0,1}\d*\.{0,1}\d+$/',
                'message' => 'You must enter a valid numeric'
            );
        } else {
            $data = array(
                'pattern' => '/^\d*\.{0,1}\d+$/',
                'message' => 'You must enter a valid positive numeric'
            );
        }

        if (!preg_match($data['pattern'], $value->getValue())) {
            $this->context->addViolation($data['message']);
        }
    }
}
