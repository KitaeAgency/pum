<?php

namespace Pum\Core\Definition\Archive;

use Pum\Core\Definition\Beam;
use Pum\Core\ObjectFactory;
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
    protected $filename;

    public function __construct()
    {
        $this->createTempZip();
        register_shutdown_function(
            function () {
               unlink($this->filename);
            }
        );
    }

    /**
     * @param Beam $beam
     * @param ObjectFactory $manager
     * @param bool $exportExternals
     * @return string
     */
    public function createFromBeam(Beam $beam, ObjectFactory $manager, $exportExternals = false)
    {
        $beams = array(
            'main' => $beam->getName(),
            'related' => array()
        );

        $this->zipArchive->addFromString(
            $beam->getName().".json",
            json_encode($beam->toArray(), JSON_PRETTY_PRINT)
        );

        if ($beam->hasExternalRelations($manager) && $exportExternals) {
            foreach ($beam->getExternalRelations($manager) as $relation) {
                $beams['related'][] = $relation->getToObject()->getBeam()->getName();
                $this->zipArchive->addFromString(
                    $relation->getToObject()->getBeam()->getName().".json",
                    json_encode($relation->getToObject()->getBeam()->toArray(), JSON_PRETTY_PRINT)
                );
            }
        }

        $this->zipArchive->addFromString(
            "manifest.json",
            json_encode($beams, JSON_PRETTY_PRINT)
        );

        $this->zipArchive->close();

        return $this->filename;
    }

    /**
     * Init \ZipArchive object with a temp name
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    protected function createTempZip()
    {
        $this->zipArchive = new \ZipArchive();
        $this->filename = sys_get_temp_dir() .md5(mt_rand()).'.zip';

        if ($this->zipArchive->open($this->filename, \ZipArchive::OVERWRITE)!== true) {
            throw new IOException('Could not write zip archive into tmp folder');
        }
    }
}
