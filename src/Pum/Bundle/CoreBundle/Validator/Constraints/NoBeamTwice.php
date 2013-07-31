<?php

namespace Pum\Bundle\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NoBeamTwice extends Constraint
{
    public $message = 'Beam %name% is already present in project';
}
