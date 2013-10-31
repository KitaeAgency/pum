<?php

namespace Pum\Bundle\TypeExtraBundle\Validator\Constraints;

use Symfony\Component\HttpFoundation\File\File as FileObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\FileValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MediaValidator extends FileValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        $value = $value->getFile();

        /* To be more specific */
        switch ($constraint->type) {
            case "image":
                $constraint->mimeTypes = array(
                    'image/gif',
                    'image/png',
                    'image/jpg',
                    'image/jpeg'
                );

                break;

            case "video":
                $constraint->mimeTypes = 'video/*';

                break;

            case "pdf":
                $constraint->mimeTypes = array(
                    'application/pdf',
                    'x-pdf'
                );

                break;

            case "file":
                $constraint->mimeTypes = array();

                break;
        }


        return parent::validate($value, $constraint);
    }
}
