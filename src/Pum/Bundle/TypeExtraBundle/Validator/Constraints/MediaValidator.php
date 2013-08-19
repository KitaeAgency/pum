<?php

namespace Pum\Bundle\TypeExtraBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 *
 * @api
 */
class MediaValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        switch ($constraint->type) {
            case "image":
                
                break;
            case "video":
                
                break;
            case "pdf":
                
                break;
            case "file":
                
                break;
        }
    }
}
