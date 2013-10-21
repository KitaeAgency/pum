<?php

namespace Pum\Core\Extension\View;

use Pum\Core\Extension\View\Storage\ViewStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractViewFeature implements ViewFeatureInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ViewStorageInterface
     */
    protected $view;

    /**
     * Constructor.
     *
     * @param 
     */
    public function __construct(ContainerInterface $container, ViewStorageInterface $view)
    {
        $this->container = $container;
        $this->view      = $view;
    }

    /**
     * {@inheritdoc}
     */
    public function getViewTemplates($type = null)
    {
        return $this->view->getAllPaths($type);
    }

    protected function getPumTemplatesFolders()
    {
        $container = $this->container;

        $folders = array();
        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
            if (is_dir($dir = $container->getParameter('kernel.root_dir').'/Resources/'.$bundle.'/pum_views/')) {
                $folders[] = $dir;
            }

            $reflection = new \ReflectionClass($class);
            if (is_dir($dir = dirname($reflection->getFilename()).'/Resources/pum_views/')) {
                $folders[] = $dir;
            }
        }

        return $folders;
    }

    protected function guessPumPath($realPath)
    {
        $pumPath  = explode('\pum_views\\', $realPath);

        if (count($pumPath) > 1) {
            unset($pumPath[0]);
            $pumPath = str_replace('\\', '/', implode('', $pumPath));
            
            if ($pumPath) {
                return $pumPath;
            }
        }

        return false;
    }
}