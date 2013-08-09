<?php

namespace Pum\Bundle\TypeExtraBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 *
 * @api
 */
class Coordinate extends Constraint
{
    public $message = 'You must enter a valid coordinate';
}
