<?php

namespace Pum\Core\Extension\View\Loader;

class PumFilesystemLoader extends \Twig_Loader_Filesystem
{
    const PATH_PREFIX = 'pum://';

    /**
     * Constructor.
     *
     * @param $folders $folders Collection of folders to find templates
     */
    public function __construct($folders = array())
    {
        parent::__construct($folders);
    }

    /**
     * {@inheritdoc}
     */
    protected function findTemplate($template)
    {
        $pum_prefix = self::PATH_PREFIX;

        $pos = strpos(strtolower($template), $pum_prefix);
        if ($pos === false || $pos !== 0) {
            throw new \Twig_Error_Loader(sprintf('Invalid pum template name "%s".', $template), -1, null, null);
        }

        $template = substr($template, $pos+strlen($pum_prefix));

        return parent::findTemplate($template);
    }
}