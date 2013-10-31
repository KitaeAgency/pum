<?php

namespace Pum\Bundle\TypeExtraBundle\Media;

interface FlushStorageInterface
{
    public function flushToStorage(StorageInterface $storage);
}
