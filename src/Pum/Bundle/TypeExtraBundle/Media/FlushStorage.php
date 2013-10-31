<?php

namespace Pum\Bundle\TypeExtraBundle\Media;

interface FlushStorage
{
    public function flushToStorage(StorageInterface $storage);
}
