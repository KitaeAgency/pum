<?php

namespace Pum\Bundle\WoodworkBundle\Validation\Constraints;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class BeamArchiveStructureValidator
 * @package Pum\Core\Extension\Validation\Constraints
 */
class BeamArchiveStructureValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {

        if (null === $value) {
            return;
        }

        $zipArchive = new \ZipArchive();

        if ($zipArchive->open($value->getPathName())!== true) {
            throw new IOException('Could not read uploaded archive');
        }

        if (!$zipArchive->statName($constraint->manifestFileName)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
