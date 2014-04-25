<?php

namespace Pum\Bundle\WoodworkBundle\Filesystem;

use Pum\Core\Definition\Archive\ZipArchive;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class BeamZipStorage
 * @package Pum\Bundle\WoodworkBundle\Filesystem
 */
class ZipStorage
{
    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @param $cacheDir
     */
    public function __construct($cacheDir)
    {
        $this->filesystem = new Filesystem();

        $cacheDir .= '/zipArchive/';

        if (!$this->filesystem->exists($cacheDir)) {
            $this->filesystem->mkdir($cacheDir, 0777);
        }

        $this->cacheDir = $cacheDir;

    }

    /**
     * @param ZipArchive $archive
     * @return string
     */
    public function saveZip(ZipArchive $archive)
    {
        $zipId = md5(mt_rand());

        $this->filesystem->rename($archive->getPath(), $this->cacheDir.$zipId.'.zip');

        return $zipId;
    }

    /**
     * @param $zipId
     * @return ZipArchive
     * @throws \InvalidArgumentException
     */
    public function getZip($zipId)
    {
        $zipPath = $this->cacheDir.$zipId.'.zip';
        if (!$this->filesystem->exists($zipPath)) {
            throw new \InvalidArgumentException('Zip file not found');
        }
        $archive = new ZipArchive($this->cacheDir.$zipId.'.zip');

        return $archive;
    }
}
