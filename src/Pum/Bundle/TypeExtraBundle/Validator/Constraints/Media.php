<?php

namespace Pum\Bundle\TypeExtraBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 *
 * @api
 */
class Media extends Constraint
{
    public $message = 'Your media is invalid';
}
