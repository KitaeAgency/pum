<?php

namespace Pum\Bundle\TypeExtraBundle\Media;

interface StorageInterface
{
    /**
     * Returns asset path
     *
     * @return string
     */
    public function store($file);

    /**
     * Returns file.
     *
     * @return file
     *
     * @throws Pum\TypeExtraBundle\Exception\MediaNotFoundException
     */
    public function getFile($path);

    /**
     *
     * @return boolean
     */
    public function remove($file);

    /**
     *
     * @return boolean
     */
    public function exists($file);
}
