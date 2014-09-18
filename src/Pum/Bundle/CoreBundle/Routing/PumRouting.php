<?php

namespace Pum\Bundle\CoreBundle\Routing;

use Pum\Core\Extension\Routing\RoutableInterface;
use Pum\Core\Extension\Routing\RoutingTable;
use Pum\Core\Extension\EmFactory\Doctrine\ObjectEntityManager;
use Symfony\Component\HttpFoundation\Request;

class PumRouting
{
    protected $routingGenerator;
    protected $routingTable;
    protected $oem;

    public function __construct(PumSeoGenerator $routingGenerator, RoutingTable $routingTable, ObjectEntityManager $oem)
    {
        $this->routingGenerator = $routingGenerator;
        $this->routingTable     = $routingTable;
        $this->oem              = $oem;
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
        return $this->oem;
    }

    /**
     * @return PumRouting
     */
    public function setOEM(ObjectEntityManager $oem)
    {
        $this->oem =$oem;

        return $this;
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
        $vars = array();

        // Get vars from request
        if (null !== $request) {
            $vars = $request->query->all();
        }

        // Get parts from url
        $parts = explode('/', $seoKey);

        // Convert seo parts into objects
        foreach ($parts as $part) {
            if (false === strpos($part, '=')) {
                $this->addObjectToVars($vars, $part);
            }
        }

        // Convert seo parts into vars
        foreach ($parts as $part) {
            if (false !== strpos($part, '=')) {
                $this->addVarToVars($vars, $part);
            }
        }

        $template = $this->getRoutingGenerator()->getTemplate($vars);

        return array($template, $vars);
    }

    private function addVarToVars(&$vars, $var)
    {
        $data = explode('=', $var, 2);
        if (!isset($vars[$data[0]])) {
            $vars[$data[0]] = $data[1];
        }
    }

    private function addObjectToVars(&$vars, $seoKey)
    {
        if ($seoKey) {
            $id = $this->getRoutingTable()->match($seoKey);

            if (null === $id) {
                throw $this->createNotFoundException('No element in routing table with key '.$seoKey);
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

            foreach (array($objClass, 'object') as $key) {
                if (isset($vars[$key])) {
                    $i = 2;
                    while (isset($vars[$key.$i])) {
                        $i++;
                    }
                    $key = $key.$i;
                }

                $vars[$key] = $object;
            }
        }
    }
}
