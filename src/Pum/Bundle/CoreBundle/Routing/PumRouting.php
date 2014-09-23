<?php

namespace Pum\Bundle\CoreBundle\Routing;

use Pum\Core\Extension\Routing\RoutableInterface;
use Pum\Core\Extension\Routing\RoutingTable;
use Pum\Bundle\CoreBundle\PumContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
    public function generate($seoKey, array $parameters = array(), $routeName = null, $seoKeyName = null, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->routingGenerator->generate($seoKey, $parameters, $routeName, $seoKeyName, $referenceType);
    }

    /**
     * @return list(template, vars)
     */
    public function handleSeo($seoKey)
    {
        // Errors
        $errors        = array();

        // Get vars from seo
        $paramsVars    = $this->getVarsFromSeo($seoKey);
        $paramsObjects = $this->getObjectFromSeo($seoKey, $errors);

        // Get template from objects
        $template = $this->getRoutingGenerator()->getTemplate($paramsObjects, $this->getConfig()->get('ww_reverse_seo_object_template_handler', false));

        // Add not found template error
        if (null == $template) {
            $errors[] = array(
                'type'    => 'template',
                'message' => 'No template found for seo '.$seoKey
            );
        }

        // Generate incremente objects
        $paramsObjects = $this->addOrderedObjects($paramsObjects);

        return array($template, array_merge($paramsVars, $paramsObjects), $errors);
    }

    private function addOrderedObjects($paramsObjects)
    {
        if (count($paramsObjects) === 1) {
            $paramsObjects['object'] = reset($paramsObjects);
        } else {
            $i = 0;
            foreach ($paramsObjects as $paramObjects) {
                $paramsObjects['object_'.$i++] = $paramObjects;
            }
        }

        return $paramsObjects;
    }

    private function getVarsFromSeo($seoKey)
    {
        $vars  = array();

        $request = $this->context->getContainer()->get('request');
        foreach ($request->query->all() as $key => $value) {
            $vars[$key] = $this->formatVar($value);
        }

        // Delia style but not use
        /*$parts = explode('/', $seoKey);

        foreach ($parts as $part) {
            if (false !== strpos($part, '=')) {
                $data = explode('=', $part, 2);
                if (!isset($vars[$data[0]])) {
                    $vars[$data[0]] = $this->formatVar($data[1]);
                }
            }
        }*/

        return $vars;
    }

    private function formatVar($var)
    {
        switch (strtolower($var)) {
            case 'true':
                return true;

            case 'false':
                return false;

            default:
                return $var;
        }
    }

    private function getObjectFromSeo($seoKey, &$errors)
    {
        $objects = array();
        $parts   = explode('/', $seoKey);

        foreach ($parts as $part) {
            /*if ($part && false === strpos($part, '=')) {*/
                $id = $this->getRoutingTable()->match($part);

                if (null === $id) {
                    $errors[] = array(
                        'type'    => 'seo',
                        'message' => 'No element in routing table with key '.$part
                    );
                    continue;
                }

                if (false === strpos($id, ':')) {
                    $errors[] = array(
                        'type'    => 'seo',
                        'message' => 'Unexpected value found in routing table: "'.$id.'".'
                    );
                    continue;
                }

                list($objClass, $objId) = explode(':', $id, 2);

                $object = $this->getOEM()->getRepository($objClass)->find($objId);

                if (null === $object) {
                    $errors[] = array(
                        'type'    => 'seo',
                        'message' => 'Incorrect value found in routing table: "'.$id.'". Object does not exist'
                    );
                    continue;
                }

                if (!$object instanceof RoutableInterface) {
                    $errors[] = array(
                        'type'    => 'seo',
                        'message' => 'Expected a RoutableInterface object, got a '.get_class($object)
                    );
                    continue;
                }

                $objects[$objClass][] = $object;
            /*}*/
        }

        foreach ($objects as $type => $objs) {
            if (count($objs) === 1) {
                $objects[$type] = reset($objs);
            }
        }

        return $objects;
    }

}
