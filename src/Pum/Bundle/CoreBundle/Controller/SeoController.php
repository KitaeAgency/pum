<?php

namespace Pum\Bundle\CoreBundle\Controller;

use Pum\Core\Extension\Routing\RoutableInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SeoController extends Controller
{
    /**
     * @Route(name="pum_object", path="/{_project}/{key}", requirements={"key"=".*"})
     */
    public function showAction($key, Request $request)
    {
        /* Delia Seo Style */

        // Vars for template
        $vars = array();

        // Get all parts from url
        $parts = explode('/', $key);

        // Convert all parts into objects or vars
        foreach ($parts as $part) {
            if (false !== strpos($part, '=')) {
                $this->addVarToVars($vars, $part);
            } else {
                $this->addObjectToVars($vars, $part);
            }
        }

        // Get vars from request
        $vars  = array_merge($vars, $request->query->all());

        // Get template
        $templateName = $this->getTemplateFromVars($vars);

        return $this->render($templateName, $vars);
    }

    private function addObjectToVars(&$vars, $seoKey)
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

            if (!$object) {
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

    private function addVarToVars(&$vars, $var)
    {
        $data   = explode('=', $var, 2);
        $vars[$data[0]] = $data[1];
    }

    private function getTemplateFromVars($vars)
    {
        $config = $this->get('pum.config');
        if ($config->get('ww_reverse_seo_object_template_handler', false)) {
            $vars = array_reverse($vars);
        }

        foreach ($vars as $object) {
            if (is_object($object)) {
                if ($template = $object->getSeoTemplate()) {
                    return $template;
                }
            }
        }

        throw $this->createNotFoundException('No template found in objects !');
    }
}
