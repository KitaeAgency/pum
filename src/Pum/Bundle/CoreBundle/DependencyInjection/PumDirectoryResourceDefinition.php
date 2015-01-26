<?php

namespace Pum\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class PumDirectoryResourceDefinition extends Definition
{
    /**
     * Constructor.
     *
     * @param string $bundle A bundle name or empty string
     * @param string $engine The templating engine
     * @param array  $dirs   An array of directories to merge
     */
    public function __construct($bundle, $engine, array $dirs)
    {
        if (!count($dirs)) {
            throw new \InvalidArgumentException('You must provide at least one directory.');
        }

        parent::__construct();

        $this
            ->addTag('assetic.templating.'.$engine)
            ->addTag('assetic.formula_resource', array('loader' => $engine));
        ;

        if (1 == count($dirs)) {
            // no need to coalesce
            self::configureDefinition($this, $bundle, $engine, reset($dirs));
            return;
        }

        // gather the wrapped resource definitions
        $resources = array();
        foreach ($dirs as $dir) {
            $resources[] = $resource = new Definition();
            self::configureDefinition($resource, $bundle, $engine, $dir);
        }

        $this
            ->setClass('%assetic.coalescing_directory_resource.class%')
            ->addArgument($resources)
            ->setPublic(false)
        ;
    }

    /**
     * Hydrate the Definition Object.
     * @param  Definition $definition A service definition
     * @param  string     $bundle     The bundle name
     * @param  string     $engine     The templating engine
     * @param  string     $dir        The directory path
     */
    static private function configureDefinition(Definition $definition, $bundle, $engine, $dir)
    {
        $definition
            ->setClass('%assetic.directory_resource.class%')
            ->addArgument(new Reference('pum.templating.loader'))
            ->addArgument($bundle)
            ->addArgument($dir)
            ->addArgument('/\.[^.]+\.'.$engine.'$/')
            ->setPublic(false)
        ;
    }
}
