<?php

namespace Pum\Core\Extension\Validation\Constraints;

use Symfony\Component\Validator\Constraints\DateTime as SFDateTime;

/**
 * @Annotation
 *
 *
 * @api
 */
class DateTime extends SFDateTime
{
    public $restriction               = null;
    public $posteriorDateErrorMessage = 'This {{ value }} is not a valid posterior datetime.';
    public $anteriorDateErrorMessage  = 'This {{ value }} is not a valid anterior datetime.';
}
