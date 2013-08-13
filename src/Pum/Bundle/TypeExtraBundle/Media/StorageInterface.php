<?php

namespace Pum\Bundle\TypeExtraBundle\Media;

interface StorageInterface
{
    /**
     * Returns file.
     *
     * @return file
     *
     * @throws Pum\TypeExtraBundle\Exception\MediaNotFoundException
     */
    public function getFile($path);

    /**
     * Returns asset path
     *
     * @return string
     */
    public function store($file);

    public function remove($file);
}
