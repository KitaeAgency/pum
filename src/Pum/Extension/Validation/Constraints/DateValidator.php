<?php

namespace Pum\Core\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\DateValidator as SFDateValidator;
use Pum\Bundle\WoodworkBundle\Form\Type\FieldType\DateType;

/**
 *
 * @api
 */
class DateValidator extends SFDateValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }
        
        parent::validate($value, $constraint);

        if ($constraint->restriction) {
            if (!$value instanceof \DateTime) {
                $value = new DateTime($value);
            }
            if (DateType::POSTERIOR_DATE == $constraint->restriction 
                && $value->getTimestamp() < time()) {
                    $this->context->addViolation($constraint->posteriorDateErrorMessage, array('{{ value }}' => $value->format('Y-m-d H:i:s')));
            } elseif (DateType::ANTERIOR_DATE == $constraint->restriction 
                && $value->getTimestamp() > time()) {
                    $this->context->addViolation($constraint->anteriorDateErrorMessage, array('{{ value }}' => $value->format('Y-m-d H:i:s')));
            }
        }
    }
}
