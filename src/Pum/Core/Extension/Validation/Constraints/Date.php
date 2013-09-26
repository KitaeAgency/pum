<?php

namespace Pum\Core\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Date as SFDate;

/**
 * @Annotation
 *
 *
 * @api
 */
class Date extends SFDate
{
    public $restriction               = null;
    public $posteriorDateErrorMessage = 'This {{ value }} is not a valid posterior date.';
    public $anteriorDateErrorMessage  = 'This {{ value }} is not a valid anterior date.';
}
