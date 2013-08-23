<?php

namespace Pum\Core\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 *
 * @api
 */
class Decimal extends Constraint
{
    public $message = 'You must enter a valid decimal';
}
