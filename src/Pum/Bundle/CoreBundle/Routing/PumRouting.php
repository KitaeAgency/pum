<?php

namespace Pum\Bundle\CoreBundle\Routing;

use Pum\Core\Extension\Routing\RoutableInterface;
use Pum\Core\Extension\Routing\RoutingTable;
use Pum\Bundle\CoreBundle\PumContext;
use Symfony\Component\HttpFoundation\Request;

class PumRouting
{
    protected $routingGenerator;
    protected $routingTable;
    protected $context;

    public function __construct(PumContext $context, PumSeoGenerator $routingGenerator, RoutingTable $routingTable)
    {
        $this->context          = $context;
        $this->routingGenerator = $routingGenerator;
        $this->routingTable     = $routingTable;
    }

    /**
     * @return PumSeoGenerator
     */
    public function getRoutingGenerator()
    {
        return $this->routingGenerator;
    }

    /**
     * @return RoutingTable
     */
    public function getRoutingTable()
    {
        return $this->routingTable;
    }

    /**
     * @return ObjectEntityManager
     */
    public function getOEM()
    {
        return $this->context->getProjectOEM();
    }

    /**
     * @return MysqlConfig
     */
    public function getConfig()
    {
        return $this->context->getProjectConfig();
    }

    /**
     * @return string
     */
    public function generate($seoKey, $parameters = array(), $routeName = null, $referenceType = self::ABSOLUTE_PATH)
    {
        return $this->routingGenerator->generate($seoKey, $parameters, $routeName, $referenceType);
    }

    /**
     * @return list(template, vars)
     */
    public function getParameters($seoKey, Request $request = null)
    {
        // Vars for template
        $paramsQueries = array();
        $errors        = array();

        // Get vars from request
        if (null !== $request) {
            $paramsQueries = $request->query->all();
        }

        // Get vars from seo
        $paramsVars    = $this->getVarsFromSeo($seoKey);
        $paramsObjects = $this->getObjectFromSeo($seoKey);

        $template = $this->getRoutingGenerator()->getTemplate($paramsObjects, $this->getConfig()->get('ww_reverse_seo_object_template_handler', false));

        if (count($paramsObjects) === 1) {
            $paramsObjects['object'] = reset($paramsObjects);
        } else {
            $i = 0;
            foreach ($paramsObjects as $paramObjects) {
                $paramsObjects['object_'.$i++] = $paramObjects;
            }
        }

        $vars = array_merge($paramsQueries, $paramsVars, $paramsObjects);

        return array($template, $vars, $errors);
    }

    private function getVarsFromSeo($seoKey)
    {
        $vars  = array();
        $parts = explode('/', $seoKey);

        foreach ($parts as $part) {
            if (false !== strpos($part, '=')) {
                $data = explode('=', $part, 2);
                if (!isset($vars[$data[0]])) {
                    $vars[$data[0]] = $data[1];
                }
            }
        }

        return $vars;
    }

    private function getObjectFromSeo($seoKey)
    {
        $objects = array();
        $parts   = explode('/', $seoKey);
        $count   = 0;

        foreach ($parts as $part) {
            if ($part && false === strpos($part, '=')) {
                $count++;
            }
        }

        foreach ($parts as $part) {
            if ($part && false === strpos($part, '=')) {
                $id = $this->getRoutingTable()->match($part);

                if (null === $id) {
                    throw new \RuntimeException('No element in routing table with key '.$part);
                }

                if (false === strpos($id, ':')) {
                    throw new \RuntimeException('Unexpected value found in routing table: "'.$id.'".');
                }

                list($objClass, $objId) = explode(':', $id, 2);

                $object = $this->getOEM()->getRepository($objClass)->find($objId);

                if (null === $object) {
                    throw new \RuntimeException('Incorrect value found in routing table: "'.$id.'". Maybe object does not exist?');
                }

                if (!$object instanceof RoutableInterface) {
                    throw new \RuntimeException('Expected a RoutableInterface object, got a '.get_class($object));
                }

                if ($count === 1) {
                    $objects[$objClass] = $object;
                } else {
                    $i = 0;
                    if (isset($objects[$objClass.'_'.$i])) {
                        while (isset($objects[$objClass.'_'.$i])) {
                            $i++;
                        }
                    }

                    $objects[$objClass.'_'.$i] = $object;
                }
            }
        }

        return $objects;
    }

}
