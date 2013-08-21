<?php

namespace Pum\Bundle\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NoBeamWithSameName extends Constraint
{
    public $message = 'Beam with name %name% already exists';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'beam_name_unique';
    }
}
