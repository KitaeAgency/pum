<?php

namespace Pum\Bundle\TypeExtraBundle\Media;

use Pum\Bundle\TypeExtraBundle\Exception\MediaNotFoundException;

/**
 * Implementation of driver using a PHP array (no persistence).
 */
class FilesystemStorage implements StorageInterface
{
    protected $directory;
    protected $path;

    public function __construct($directory, $path)
    {
        $this->directory = $directory;
        $this->path      = $path;
    }

    public function store($file)
    {
        return $this->getPath().$file;
    }

    public function getFile($path)
    {
        $file = $this->$directory.$path;

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
            exit;
        }

        throw new MediaNotFoundException($file);
    }
}
