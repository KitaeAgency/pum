<?php

namespace Pum\Core\Extension\View\Loader;

class PumFilesystemLoader extends \Twig_Loader_Filesystem
{
    const PATH_PREFIX = 'pum://';

    protected $cacheExists;
    protected $cacheNotExists;

    /**
     * Constructor.
     *
     * @param $folders $folders Collection of folders to find templates
     */
    public function __construct($folders = array())
    {
        $this->cache          = array();
        $this->cacheNotExists = array();

        parent::__construct($folders);
    }

    /**
     * {@inheritdoc}
     */
    protected function findTemplate($template)
    {
        $logicalName = (string) $template;

        if (isset($this->cache[$logicalName])) {
            return $this->cache[$logicalName];
        } else if (isset($this->cacheNotExists[$logicalName])) {
            throw new \Twig_Error_Loader(sprintf('Unable to find template "%s".', $logicalName));
        }

        $pum_prefix = self::PATH_PREFIX;

        $pos = strpos(strtolower($template), $pum_prefix);
        if ($pos === false || $pos !== 0) {
            throw new \Twig_Error_Loader(sprintf('Invalid pum template name "%s".', $template), -1, null, null);
        }

        $template = substr($template, $pos+strlen($pum_prefix));

        try {
            return $this->cache[$logicalName] = parent::findTemplate($template);
        } catch (\Twig_Error_Loader $e) {
            $this->cacheNotExists[$logicalName] = true;

            throw new \Twig_Error_Loader(sprintf('Unable to find template "%s".', $logicalName));
        }
    }
}