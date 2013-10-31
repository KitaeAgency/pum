<?php

namespace Pum\Bundle\CoreBundle\Controller;

use Pum\Core\Extension\Routing\RoutableInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SeoController extends Controller
{
    /**
     * @Route(name="pum_object", path="/{_project}/{key}", requirements={"key"=".*"})
     */
    public function showAction($key)
    {
        $parts = explode('/', $key);
        $seoKey = array_pop($parts);
        $id = $this->get('pum.routing')->getRoutingTable()->match($seoKey);

        if (null === $id) {
            throw $this->createNotFoundException('No element in routing table with key '.$seoKey);
        }

        if (false === strpos($id, ':')) {
            throw new \RuntimeException('Unexpected value found in routing table: "'.$id.'".');
        }

        list($objClass, $objId) = explode(':', $id, 2);

        $object = $this->get('pum.oem')->getRepository($objClass)->find($objId);

        if (!$object) {
            throw new \RuntimeException('Incorrect value found in routing table: "'.$id.'". Maybe object does not exist?');
        }

        if (!$object instanceof RoutableInterface) {
            throw new \RuntimeException('Expected a RoutableInterface object, got a '.get_class($object));
        }

        return $this->render($object->getSeoTemplate(), array(
            $objClass => $object,
            'object'  => $object
        ));
    }
}
