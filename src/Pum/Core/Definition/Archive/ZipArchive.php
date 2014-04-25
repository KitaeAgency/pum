<?php

namespace Pum\Core\Definition\Archive;

use Pum\Core\Definition\Beam;
use Pum\Core\ObjectFactory;
use Pum\Core\Schema\SchemaInterface;
use Symfony\Component\Filesystem\Exception\IOException;

class ZipArchive
{
    /**
     * @var \ZipArchive
     */
    protected $zipArchive;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $manifestFileName;

    public function __construct($path = null, $manifestFileName = "manifest.json")
    {
        $this->zipArchive = new \ZipArchive();

        if ($path === null) {
            $this->createTempZip();
            $flag = \ZipArchive::OVERWRITE;
        } else {
            $this->path = $path;
            $flag = null;
        }

        if ($this->zipArchive->open($this->path, $flag)!== true) {
            throw new IOException('Could not write zip archive into tmp folder');
        }

        $this->manifestFileName = $manifestFileName;
    }

    /**
     * @param Beam $beam
     * @param SchemaInterface $schema
     * @param bool $exportExternals
     * @return ZipArchive
     */
    public static function createFromBeam(Beam $beam, SchemaInterface $schema, $exportExternals = false)
    {
        $archive = new self();

        $beams = array(
            'main' => $beam->getName(),
            'related' => array()
        );

        $archive->zipArchive->addFromString(
            $beam->getName().".json",
            json_encode($beam->toArray(), JSON_PRETTY_PRINT)
        );

        if ($beam->hasExternalRelations($schema) && $exportExternals) {
            foreach ($beam->getExternalRelations($schema) as $relation) {
                $beams['related'][] = $relation->getToObject()->getBeam()->getName();
                $archive->zipArchive->addFromString(
                    $relation->getToObject()->getBeam()->getName().".json",
                    json_encode($relation->getToObject()->getBeam()->toArray(), JSON_PRETTY_PRINT)
                );
            }
        }

        $archive->zipArchive->addFromString(
            $archive->manifestFileName,
            json_encode($beams, JSON_PRETTY_PRINT)
        );

        $archive->zipArchive->close();

        return $archive;
    }

    public function getBeamListFromZip()
    {
        $files = array();

        for ($i = 0; $i < $this->zipArchive->numFiles; $i++) {
            $stat = $this->zipArchive->statIndex($i);
            if ($stat['name'] != $this->manifestFileName) {
                $files[] = $stat['name'];
            }
        }

        return $files;
    }

    /**
     * Init \ZipArchive object with a temp name
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    protected function createTempZip()
    {
        $this->path = sys_get_temp_dir() .'/'. md5(mt_rand()).'.zip';

        register_shutdown_function(
            function () {
                unlink($this->path);
            }
        );
    }

    /**
     * @return null|string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getFileByName($name)
    {
        return $this->zipArchive->getFromName($name);
    }

    /**
     * @return mixed
     */
    public function getManifest()
    {
        return json_decode($this->getFileByName($this->manifestFileName), true);
    }

    /**
     *
     */
    public function closeArchive()
    {
        $this->zipArchive->close();
    }
}
