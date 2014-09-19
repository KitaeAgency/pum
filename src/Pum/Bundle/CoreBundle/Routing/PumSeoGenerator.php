<?php

namespace Pum\Bundle\CoreBundle\Routing;

use Pum\Core\Extension\Routing\RoutableInterface;
use Pum\Core\Extension\Routing\RoutingTable;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class PumSeoGenerator implements UrlGeneratorInterface
{
    protected $urlGenerator;
    protected $routeName;
    protected $routingTable;

    public function __construct(UrlGeneratorInterface $urlGenerator, $routeName = 'pum_object')
    {
        $this->urlGenerator = $urlGenerator;
        $this->routeName    = $routeName;
    }

    public function generate($name, $parameters = array(), $routeName = null, $referenceType = self::ABSOLUTE_PATH)
    {
        // Generate basic route
        if (!$name instanceof RoutableInterface && !is_array($name)) {
            return $this->urlGenerator->generate($name, $parameters, $referenceType);
        }

        // Generate Pum Seo
        $orderedSeoKeys = $this->getOrderedSeo($name);
        $seo            = implode('/', $orderedSeoKeys);
        $parameters     = array_merge($parameters, array('seo' => $seo));

        // Resolve route name
        if (null === $routeName) {
            $routeName = $this->routeName;
        }

        return $this->urlGenerator->generate($routeName, $parameters, $referenceType);
    }

    public function getTemplate($objs, $reverse = false)
    {
        $templates = $this->getOrderedSeo($objs, false);

        var_dump($reverse, $templates);die;

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

    private function getOrderedSeo($objs, $fieldKey = true)
    {
        if (!$objs instanceof RoutableInterface && !is_array($objs)) {
            return array();
        }

        $objs    = $objs instanceof RoutableInterface ? array($objs) : $objs;
        $seoKeys = array();

        foreach ($objs as $obj) {
            if (!$obj instanceof RoutableInterface) {
                continue;
            }

            if ($fieldKey) {
                $seoKeys[$obj->getSeoOrder()][] = $obj->getSeoKey();
            } else {
                $seoKeys[$obj->getSeoOrder()][] = $obj->getSeoTemplate();
            }
        }

        ksort($seoKeys);

        $orderedSeo = array();
        foreach ($seoKeys as $keys) {
            foreach ($keys as $key) {
                $orderedSeo[] = $key;
            }
        }

        // Log seo call
        if ($fieldKey) {
            error_log(var_export($orderedSeo, true));
        }

        return $orderedSeo;
    }
}
