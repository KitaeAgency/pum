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
    public function handleRequest(Request $request, ObjectDefinition $object)
    {
        $this->request = $request;
        $this->object  = $object;
        $this->options  = array(
            'label_field'    => $request->query->get('tree_label_field'),
            'parent_field'   => $request->query->get('tree_parent_field'),
            'children_field' => $request->query->get('tree_children_field'),
            'node_value'     => $request->query->get('tree_node_value'),
        );

        if (!$action = $request->query->get('tree_action')) {
            return;
        }

        switch ($action) {
            case 'root':
                return $this->getRoots();
            break;

            case 'node':
                return $this->getNode($this->options['node_value']);
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

            default:
                return;
            break;
        }
    }

    private function getRoots()
    {
        $rootNode = new TreeNode($id = null, $label = '', $icon = null, $type = null, $isRoot = true);
        $rootNode = $this->populateNode($rootNode, $detail = true);

        return new JsonResponse($rootNode->toArray());
    }

    private function getNode()
    {
        $nodes       = $this->getNodes();
        $id          = $this->options['node_value'];
        $label_field = 'get'.ucfirst(Namer::toCamelCase($this->options['label_field']));

        if (!$id || null === $object = $this->getRepository($this->object->getName())->find($id)) {
            return new Response('ERROR');
        }

        $treeNode = new TreeNode($id, $object->$label_field());
        $treeNode = $this->populateNode($treeNode, in_array($object->getId(), $nodes));

        return new JsonResponse($treeNode->toArray());
    }

    private function populateNode(TreeNode $treeNode, $detail)
    {
        $nodes          = $this->getNodes();
        $parent_field   = $this->options['parent_field'];
        $children_field = $this->options['children_field'];
        $label_field    = 'get'.ucfirst(Namer::toCamelCase($this->options['label_field']));

        $treeNode->setChildrenDetail($detail);

        if ($detail) {
            foreach ($this->getRepository($this->object->getName())->findBy(array($parent_field => $treeNode->getId())) as $object) {
                $childNode = new TreeNode($object->getId(), $object->$label_field());
                $childNode = $this->populateNode($childNode, in_array($object->getId(), $nodes));

                $treeNode->addChild($childNode);
                $treeNode->setHasChildren(true);
            }
        } elseif ($this->getRepository($this->object->getName())->countBy(array($parent_field => $treeNode->getId()))) {
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
        if (!$node_id) {
            return new Response('ERROR');
        }

        $values = $this->getNodes();

        if (!in_array($node_id, $values)){
            $values[] = $node_id;

            return $this->storeCookie($values);
        }

        return new Response('ERROR');
    }

    private function removeNode($node_id)
    {
        if (!$node_id) {
            return new Response('ERROR');
        }

        $values = $this->getNodes();

        if(($key = array_search($node_id, $values)) !== false) {
            unset($values[$key]);

            return $this->storeCookie($values);
        }

        return new Response('ERROR');
    }

    private function storeCookie($values, $time = null)
    {
        if (null === $time) {
            $time = time() + 3600 * 24 * 7;
        }

        $cookie   = new Cookie($this->getTreeNameSpace(), serialize($values), $time);
        $response = new Response('OK');

        $response->headers->setCookie($cookie);

        return $response->send();
    }

    private function clearCookie()
    {
        $response = new Response('OK');
        $response->headers->clearCookie($this->getTreeNameSpace());

        return $response->send();
    }
}
