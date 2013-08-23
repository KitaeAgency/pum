<?php

namespace Pum\Bundle\WoodworkBundle\Form\DataClass;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;
// use Pum\Bundle\WoodworkBundle\Validator\Constraints\BeamImport as BeamImportConstraint;


use Symfony\Component\HttpFoundation\File\File as FileObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Beam import form
 */
class BeamImport
{
    /**
     * Beam new name
     */
    protected $name;

    /**
     * Beam json file
     */
    protected $file;

    public function getName()
    {
        return $this->name;
    }

    public function getFile()
    {
        return $this->file;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new NotBlank());
        $metadata->addPropertyConstraint('file', new File());
        $metadata->addPropertyConstraint('file', new NotBlank(array('message' => 'Please select a file')));
    }
}