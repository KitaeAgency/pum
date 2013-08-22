<?php

namespace Pum\Bundle\TypeExtraBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\File;

/**
 * @Annotation
 */
class Media extends File
{
    public $type;
}
