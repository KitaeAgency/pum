<?php

namespace Pum\Bundle\CoreBundle\Routing;

use Pum\Bundle\CoreBundle\PumContext;
use Symfony\Component\Finder\Finder;

class PumTemplateFinder
{
    protected $context;
    protected $templates;

    public function __construct(PumContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return instance service
     */
    public function get($service)
    {
        return $this->context->getContainer()->get($service);
    }

    /**
     * @return string
     */
    public function getParameter($key)
    {
        return $this->context->getContainer()->getParameter($key);
    }

    /**
     * @return array
     */
    public function getRoutingTemplates()
    {
        if (null !== $this->templates) {
            return $this->templates;
        }

        $rootDir = $this->getParameter('kernel.root_dir');
        $bundles = $this->getParameter('kernel.bundles');

        $templates = array();
        $folders   = array();
        foreach ($bundles as $bundle => $class) {
            if (is_dir($dir = $rootDir.'/Resources/'.$bundle.'/pum_views')) {
                $folders[] = $dir;
            }

            $reflection = new \ReflectionClass($class);
            if (is_dir($dir = dirname($reflection->getFilename()).'/Resources/pum_views')) {
                $folders[] = $dir;
            }
        }

        $finder = new Finder();
        $finder->in($folders);
        $finder->files()->name('*.twig');
        $finder->files()->contains('{# root #}');

        foreach ($finder as $file) {
            $templates[] = 'pum://'.str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname());
        }

        return $this->templates = $templates;
    }

}
