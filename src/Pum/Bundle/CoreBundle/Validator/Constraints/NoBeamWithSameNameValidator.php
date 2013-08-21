<?php

namespace Pum\Bundle\CoreBundle\Validator\Constraints;

use Pum\Core\SchemaManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NoBeamWithSameNameValidator extends ConstraintValidator
{
    protected $schemaManager;

    public function __construct(SchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($this->schemaManager->getBeam($value)) {
            $this->context->addViolation($constraint->message, array('%name%' => $value));
        }
    }
}
