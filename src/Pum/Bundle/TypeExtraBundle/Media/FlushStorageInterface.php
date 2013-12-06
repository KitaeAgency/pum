<?php

namespace Pum\Bundle\TypeExtraBundle\Media;

interface FlushStorageInterface
{
    public function flushToStorage(StorageInterface $storage);
    public function removeFromStorage(StorageInterface $storage);
}
