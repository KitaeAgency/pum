<?php

namespace Pum\Bundle\TypeExtraBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 *
 * @api
 */
class Price extends Constraint
{
    public $allowNegativePrice = false;
}
