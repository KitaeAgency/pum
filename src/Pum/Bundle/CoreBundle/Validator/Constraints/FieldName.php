<?php

namespace Pum\Bundle\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class FieldName extends Constraint
{
    public $message = 'Fieldname "{{ value }}" is not correct fieldname. Fieldname must follow the same rules as other labels in PHP. A valid variable name starts with a letter or underscore, followed by any number of letters, numbers, or underscores. As a regular expression, it would be expressed thus: [a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
}
