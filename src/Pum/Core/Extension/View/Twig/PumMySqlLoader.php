<?php

namespace Pum\Core\Extension\View\Twig;

use Pum\Core\Extension\View\Template;
use Pum\Core\Extension\View\ViewStorageInterface;

class PumMySqlLoader implements \Twig_LoaderInterface, \Twig_ExistsLoaderInterface
{
    const PATH_PREFIX = 'pum://';

    protected $view;
    protected $cache;

    /**
     * Constructor.
     *
     * @param 
     */
    public function __construct(ViewStorageInterface $view)
    {
        $this->view  = $view;
        $this->cache = $view->getAllPaths($asArrayKey = true);
    }

    /**
     * {@inheritdoc}
     */
    public function getSource($path)
    {
        $template = $this->view->getTemplate($this->findTemplate($path));

        return $template->getSource();
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($path)
    {
        return md5($path);
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($path, $time)
    {
        $template = $this->view->getTemplate($this->findTemplate($path));

        return $template->getTime() <= $time;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($path)
    {
        try {
            $this->findTemplate($path);

            return true;
        } catch (\Twig_Error_Loader $exception) {
            return false;
        }
    }

    protected function findTemplate($path)
    {
        $pum_prefix = self::PATH_PREFIX;

        $pos = strpos(strtolower($path), $pum_prefix);
        if ($pos === false || $pos !== 0) {
            throw new \Twig_Error_Loader(sprintf('Invalid pum template name "%s".', $path), -1, null, null);
        }

        $path = substr($path, $pos+strlen($pum_prefix));

        if (isset($this->cache[$path])) {
            return $path;
        }

        throw new \Twig_Error_Loader(sprintf('Unable to find pum template "%s"', $path));
    }
}