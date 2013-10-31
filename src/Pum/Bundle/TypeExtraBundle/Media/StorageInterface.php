<?php

namespace Pum\Bundle\TypeExtraBundle\Media;

interface StorageInterface
{
    /**
     *
     * Returns asset path
     * @return string
     *
     */
    public function store(\SplFileInfo $file);

    /**
     * @return string
     */
    public function getFile($path);

    /**
     * @return boolean
     */
    public function remove($file);

    /**
     * @return boolean
     */
    public function exists($file);
}
