<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Object\Tree;

use Pum\Bundle\CoreBundle\PumContext;
use Pum\Core\Extension\Util\Namer;
use Pum\Core\Definition\ObjectDefinition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;

class TreeApi
{
    protected $request;
    protected $context;
    protected $object;
    protected $options;

    /**
     * @param pum object
     */
    public function __construct(PumContext $context)
    {
        $this->context = $context;
    }

    /**
     * @param Request request
     * @param ObjectDefinition object
     */
    public function handleRequest(Request $request, ObjectDefinition $object, array $options)
    {
        $this->request = $request;
        $this->object  = $object;
        $this->options = $options;

        if (!$action = $options['action']) {
            return;
        }

        switch ($action) {
            case 'root':
            case 'node':
                if ($this->options['node_value'] == '#' || !$this->options['node_value']) {
                    $this->options['node_value'] = null;
                    return $this->getRoots();
                } else {
                    return $this->getNode($this->options['node_value']);
                }
            break;

            case 'add_node':
                return $this->addNode($this->options['node_value']);
            break;

            case 'remove_node':
                return $this->removeNode($this->options['node_value']);
            break;

            case 'clear':
                return $this->clearCookie();
            break;

            case 'cache':
                return $this->getOpenedNodes();
            break;

            default:
                return;
            break;
        }
    }

    private function getRoots()
    {
        $rootNode = new TreeNode($id = null, $label = 'root', $icon = null, $type = 'root', $isRoot = true);
        $rootNode = $this->populateNode($rootNode, $detail = true);

        return new JsonResponse($rootNode->toArray());
    }

    private function getNode($id)
    {
        $nodes       = $this->getNodes();
        $label_field = 'get'.ucfirst(Namer::toCamelCase($this->options['label_field']));

        if (!$id || null === $object = $this->getRepository($this->object->getName())->find($id)) {
            return new Response('ERROR');
        }

        $treeNode = new TreeNode($id, $object->$label_field());
        $treeNode = $this->populateNode($treeNode, $detail = true);

        //$this->addNode($id);

        return new JsonResponse($treeNode->toArray());
    }

    private function populateNode(TreeNode $treeNode, $detail)
    {
        $nodes          = $this->getNodes();
        $parent_field   = $this->options['parent_field'];
        $children_field = $this->options['children_field'];
        $label_field    = 'get'.ucfirst(Namer::toCamelCase($this->options['label_field']));
        $em             = $this->getOEM();
        $repo           = $em->getRepository($this->object->getName());

        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $treeNode->setChildrenDetail($detail);
        $treeNode->setIcon($this->object->getTree()->getIcon());

        if ($detail) {
            foreach ($repo->findBy(array($parent_field => $treeNode->getId())) as $object) {
                $nodeDetail = in_array($object->getId(), $nodes);
                $childNode  = new TreeNode($object->getId(), $object->$label_field());
                $childNode  = $this->populateNode($childNode, $nodeDetail);

                if ($nodeDetail) {
                    $childNode->setState('opened', true);
                }

                $treeNode->addChild($childNode);
                $treeNode->setHasChildren(true);
            }

            $em->clear();
            gc_collect_cycles();

        } elseif ($repo->countBy(array($parent_field => $treeNode->getId()))) {
            $treeNode->setHasChildren(true);
        }

        return $treeNode;
    }

    private function getOEM()
    {
        return $this->context->getProjectOEM();
    }

    private function getRepository($objectName)
    {
        return $this->getOEM()->getRepository($objectName);
    }

    private function getTreeNameSpace()
    {
        if (!$this->context->getProjectName() || !$this->object) {
            throw new \RuntimeException('Context or object is missing');
        }

        return Namer::toLowercase($this->context->getProjectName().'_tree_'.$this->object->getName().'_'.$this->options['children_field']);
    }

    private function getNodes()
    {
        $values = unserialize($this->request->cookies->get($this->getTreeNameSpace()));

        if (is_array($values)) {
            return $values;
        }

        return array();
    }

    private function addNode($node_id)
    {
        $values = $this->getNodes();

        if (!$node_id || in_array($node_id, $values)) {
            return new Response('ERROR');
        }

        $values[] = $node_id;

        return $this->storeCookie($values);
    }

    private function removeNode($node_id)
    {
        $values = $this->getNodes();

        if (!$node_id || ($key = array_search($node_id, $values)) !== true) {
            return new Response('ERROR');
        }

        unset($values[$key]);

        return $this->storeCookie($values);
    }

    private function storeCookie($values, $time = null)
    {
        if (null === $time) {
            $time = time() + 3600 * 24 * 7;
        }

        $cookie   = new Cookie($this->getTreeNameSpace(), serialize($values), $time);
        $response = new Response();

        $response->headers->setCookie($cookie);

        return $response->send();
    }

    private function clearCookie()
    {
        $response = new Response();
        $response->headers->clearCookie($this->getTreeNameSpace());

        return $response->send();
    }

    private function getOpenedNodes()
    {
        $values = $this->getNodes();

        return new JsonResponse($values);
    }
}
