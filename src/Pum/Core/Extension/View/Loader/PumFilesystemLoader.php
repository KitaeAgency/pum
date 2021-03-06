<?php

namespace Pum\Core\Extension\View\Loader;

use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;

class PumFilesystemLoader extends \Twig_Loader_Filesystem
{
    const PATH_PREFIX = 'pum://';
    const MAIN_NAMESPACE = '__pum__';

    protected $parser;

    /**
     * @var array
     */
    protected $cache;

    /**
     * @var array
     */
    protected $cacheNotExists;

    /**
     * @var array
     */
    protected $normalizeNameMethod;

    /**
     * Constructor.
     *
     * @param $folders $folders Collection of folders to find templates
     */
    public function __construct($folders = array(), TemplateNameParserInterface $parser)
    {
        $this->cache          = array();
        $this->cacheNotExists = array();

        if (method_exists($this, 'normalizeName')) {
            $this->normalizeNameMethod = true;
        }

        foreach ($folders as $bundleName => $path) {
            parent::addPath($path, $bundleName);
            parent::addPath($path, self::MAIN_NAMESPACE);
        }

        $this->parser = $parser;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        try {
            $parsedName = $this->parser->parse($name);
            $nameParameters = $parsedName->all();

            $logicalName = $name;
            if (isset($nameParameters['bundle']) && isset($nameParameters['controller']) && isset($nameParameters['name']) && isset($nameParameters['format']) && isset($nameParameters['engine'])) {
                $logicalName = self::PATH_PREFIX . $nameParameters['bundle'] . '/' . $nameParameters['controller'] . '/' . $nameParameters['name'] . '.' . $nameParameters['format'] . '.' . $nameParameters['engine'];
            }
        } catch(\Exception $e) {
            $logicalName = $name;
        }
        try {
            $this->cache[(string) $name] = $this->findTemplate($logicalName);
        } catch (\Exception $e) {
            return false;
        }

        return true;
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

        // [TEMPFIX] : Symfony 2.4.2+Twig 1.15.1 new normalize method removes dual slashes
        if ($this->normalizeNameMethod === true) {
            $pum_prefix = $this->normalizeName($pum_prefix);
        }

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

    protected function parseName($name, $default = self::MAIN_NAMESPACE)
    {
        if (isset($name[0]) && '@' == $name[0]) {
            if (false === $pos = strpos($name, '/')) {
                throw new Twig_Error_Loader(sprintf('Malformed namespaced template name "%s" (expecting "@namespace/template_name").', $name));
            }

            $namespace = substr($name, 1, $pos - 1);
            $shortname = substr($name, $pos + 1);

            return array($namespace, $shortname);
        }

        return array($default, $name);
    }
}