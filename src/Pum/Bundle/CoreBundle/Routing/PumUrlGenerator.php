<?php

namespace Pum\Bundle\CoreBundle\Routing;

use Pum\Core\Extension\Routing\RoutableInterface;
use Pum\Core\Extension\Routing\RoutingTable;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class PumUrlGenerator implements UrlGeneratorInterface
{
    protected $urlGenerator;
    protected $routeName;
    protected $routingTable;

    public function __construct(UrlGeneratorInterface $urlGenerator, RoutingTable $routingTable, $routeName = 'pum_object')
    {
        $this->urlGenerator = $urlGenerator;
        $this->routingTable = $routingTable;
        $this->routeName    = $routeName;
    }

    /**
     * @return RoutingTable
     */
    public function getRoutingTable()
    {
        return $this->routingTable;
    }

    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        if (!$name instanceof RoutableInterface && !is_array($name)) {
            return $this->urlGenerator->generate($name, $parameters, $referenceType);
        }

        $orderedSeoKeys = $this->getTemplates($name);
        $key            = implode('/', $orderedSeoKeys);
        $parameters     = array_merge($parameters, array('key' => $key));

        return $this->urlGenerator->generate($this->routeName, $parameters, $referenceType);
    }

    public function getTemplates($objs)
    {
        if (!$objects instanceof RoutableInterface && !is_array($objs)) {
            return array();
        }

        $objects = $objs instanceof RoutableInterface ? array($objs) : $objs;
        $seoKeys = array();

        foreach ($objects as $object) {
            if (!$object instanceof RoutableInterface) {
                continue;
            }
            $seoKeys[$object->getSeoOrder()][] = $object->getSeoKey();
        }

        ksort($seoKeys);

        $orderedSeoKeys = array();
        foreach ($seoKeys as $keys) {
            foreach ($keys as $key) {
                $orderedSeoKeys[] = $key;
            }
        }

        error_log(var_export($orderedSeoKeys, true));

        return $orderedSeoKeys;
    }

    public function getTemplate($objs)
    {
        $templates = $this->getTemplates($objs);

        if (!empty($templates)) {
            return reset($templates);
        }

        return null;
    }

    public function getContext()
    {
        return $this->urlGenerator->getContext();
    }

    public function setContext(RequestContext $context)
    {
        return $this->urlGenerator->setContext($context);
    }
}
