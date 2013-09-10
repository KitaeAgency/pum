<?php

namespace Pum\Bundle\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 *
 * @api
 */
class SqlReservedKeywords extends Constraint
{
    public $message = 'Field name "{{ value }}" is a reserve keyword';
}
