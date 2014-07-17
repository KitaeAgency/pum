<?php

namespace Pum\Bundle\WoodworkBundle\Validation\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class BeamArchiveStructure
 * @package Pum\Core\Extension\Validation\Constraints
 */
class BeamArchiveStructure extends Constraint
{
    public $message = 'You must send a valid beam archive';

    public $manifestFileName = 'manifest.json';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}
