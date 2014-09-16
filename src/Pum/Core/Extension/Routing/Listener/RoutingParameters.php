<?php

namespace Pum\Core\Extension\Routing;

use Pum\Bundle\CoreBundle\PumContext;

class RoutingParameter
{
    protected $context;

    public function __construct(PumContext $context)
    {
        $this->context = $context;
    }

    public function getParameters($seoKey)
    {
        // Request
        $request = $this->get('request');

        // Parameters for template
        $parameters = array();

        // Get Parameters from request
        $parameters  = array_merge($parameters, $request->query->all());

        // Get Parameters parts from url
        $parts = explode('/', $seoKey);

        // Convert all parts into objects or vars
        foreach ($parts as $part) {
            if (false !== strpos($part, '=')) {
                $this->addVarToVars($parameters, $part);
            } else {
                $this->addObjectToVars($parameters, $part);
            }
        }

        return $parameters;
    }

    private function addVarToVars(&$parameters, $var)
    {
        $data                 = explode('=', $var, 2);
        $parameters[$data[0]] = $data[1];
    }

    private function addObjectToVars(&$parameters, $seoKey)
    {
        if ($seoKey) {
            $id = $this->get('pum.routing')->getRoutingTable()->match($seoKey);

            if (null === $id) {
                throw $this->createNotFoundException('No element in routing table with key '.$seoKey);
            }

            if (false === strpos($id, ':')) {
                throw new \RuntimeException('Unexpected value found in routing table: "'.$id.'".');
            }

            list($objClass, $objId) = explode(':', $id, 2);

            $object = $this->get('pum.oem')->getRepository($objClass)->find($objId);

            if (null === $object) {
                throw new \RuntimeException('Incorrect value found in routing table: "'.$id.'". Maybe object does not exist?');
            }

            if (!$object instanceof RoutableInterface) {
                throw new \RuntimeException('Expected a RoutableInterface object, got a '.get_class($object));
            }

            foreach (array($objClass, 'object') as $key) {
                if (isset($parameters[$key])) {
                    $i = 2;
                    while (isset($parameters[$key.$i])) {
                        $i++;
                    }
                    $key = $key.$i;
                }

                $parameters[$key] = $object;
            }
        }
    }

    private function get($serviceName)
    {
        return $this->context->getContainer()->get($serviceName);
    }

}
