<?php

namespace Pum\Bundle\CoreBundle\Routing;

use Pum\Core\Extension\Routing\RoutableInterface;
use Pum\Core\Extension\Routing\RoutingTable;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class PumSeoGenerator implements UrlGeneratorInterface
{
    protected $urlGenerator;
    protected $routingTable;
    protected $routeName;
    protected $seoKey;

    public function __construct(UrlGeneratorInterface $urlGenerator, $routeName = 'pum_object', $seoKey = 'seo')
    {
        $this->urlGenerator = $urlGenerator;
        $this->routeName    = $routeName;
        $this->seoKey       = $seoKey;
    }

    public function generate($name, $parameters = array(), $routeName = null, $seoKey = null, $referenceType = self::ABSOLUTE_PATH)
    {
        // Generate basic route
        if (!$name instanceof RoutableInterface && !is_array($name)) {
            if (is_object($name)) {
                if (null !== $routeName) {
                    return $this->urlGenerator->generate($routeName, $parameters, $referenceType);
                }

                throw new \Exception(sprintf('Did you forget to enable Routable behavior on %s ?', get_class($name)));
            }

            return $this->urlGenerator->generate($name, $parameters, $referenceType);
        }

        // Resolve route name
        if (null === $routeName) {
            $routeName = $this->routeName;
        }

        // Resolve seo key
        if (null === $seoKey) {
            $seoKey = $this->seoKey;
        }

        // Generate Pum Seo
        $orderedSeoKeys = $this->getOrderedSeo($name);
        $seo            = implode('/', $orderedSeoKeys);
        $parameters     = array_merge($parameters, array($seoKey => $seo));

        return $this->urlGenerator->generate($routeName, $parameters, $referenceType);
    }

    public function getTemplate($objs, $reverse = false)
    {
        $templates = $this->getOrderedSeo($objs, false);

        if (!empty($templates)) {
            $templates = reset($templates);

            if (!empty($templates)) {
                if ($reverse) {
                    return end($templates);
                }

                return reset($templates);
            }
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
        $objs    = $this->getSingleArrayValues($objs);
        $seoKeys = array();

        foreach ($objs as $obj) {
            if (!$obj instanceof RoutableInterface) {
                continue;
            }

            if ($fieldKey) {
                if ($obj->getObjectSlug()) {
                    $seoKeys[$obj->getSeoOrder()][] = $obj->getObjectSlug();
                }
            } elseif ($obj->getSeoTemplate()) {
                $seoKeys[$obj->getSeoOrder()][] = $obj->getSeoTemplate();
            }
        }

        ksort($seoKeys);

        if (!$fieldKey) {
            return $seoKeys;
        }

        $orderedSeo = array();
        foreach ($seoKeys as $keys) {
            foreach ($keys as $key) {
                $orderedSeo[] = $key;
            }
        }

        // Log seo call
        error_log(var_export($orderedSeo, true));

        return $orderedSeo;
    }

    private function getSingleArrayValues(array $array)
    {
        $result = array();

        foreach ($array as $key => $value) {
            if ($value instanceof RoutableInterface) {
                $result[] = $value;
                continue;
            }

            if (is_array($value)) {
                $result = array_merge($result, $this->getSingleArrayValues($value));
            }
        }

        return $result;
    }

}
