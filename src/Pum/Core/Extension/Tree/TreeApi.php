<?php

namespace Pum\Core\Extension\Tree;

use Pum\Bundle\CoreBundle\PumContext;
use Pum\Core\Extension\Util\Namer;
use Pum\Core\Definition\ObjectDefinition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TreeApi
{
    protected $context;
    protected $urlGenerator;

    protected $request;
    protected $object;
    protected $options;

    /**
     * @param
     */
    public function __construct(PumContext $context, UrlGeneratorInterface $urlGenerator)
    {
        $this->context      = $context;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Request request
     * @param ObjectDefinition object
     * @param array options
     */
    public function handleRequest(Request $request, ObjectDefinition $object, array $options)
    {
        if (!$action = $options['action']) {
            return;
        }

        $this->request = $request;
        $this->object  = $object;
        $this->options = $options;

        $this->getOEM()->getConnection()->getConfiguration()->setSQLLogger(null);

        switch ($action) {
            case 'root':
            case 'node':
                if ($this->options['node_value'] == '#' || $this->options['node_value'] == 'root' || !$this->options['node_value']) {
                    $this->options['node_value'] = null;

                    return $this->getRoots();
                }
                return $this->getNode($this->options['node_value']);

            case 'add_node':
                return $this->addNode($this->options['node_value']);

            case 'remove_node':
                return $this->removeNode($this->options['node_value']);

            case 'create_node':
                $label  = $request->query->get('label', null);
                $parent = $request->query->get('parent', null);

                if ('#' == $parent) {
                    $parent = null;
                }

                return $this->createNode($label, $parent);

            case 'delete_node':
                $node_id = $this->options['node_value'];

                return $this->deleteNode($node_id);

            case 'rename_node':
                $node_id = $this->options['node_value'];
                $label   = $request->query->get('label', null);

                return $this->renameNode($node_id, $label);

            case 'move_node':
                $node_id    = $this->options['node_value'];
                $new_pos    = $request->query->get('new_pos', null);
                $old_pos    = $request->query->get('old_pos', null);
                $new_parent = $request->query->get('new_parent', null);
                $old_parent = $request->query->get('old_parent', null);

                if ('#' == $new_parent) {
                    $new_parent = null;
                }

                if ('#' == $old_parent) {
                    $old_parent = null;
                }

                return $this->moveNode($node_id, $new_pos, $old_pos, $new_parent, $old_parent);

            case 'clear':
                return $this->clearCookie();

            case 'cache':
                return $this->getOpenedNodes();

            default:
                return;
        }
    }

    public function getTreeNamespace(ObjectDefinition $object = null, $children_field = null)
    {
        $object         = $object ? $object : $this->object;
        $children_field = $children_field ? $children_field : $this->options['children_field'];

        if (!$this->context->getProjectName() || !$object || !$children_field) {
            throw new \RuntimeException('Context, object or children field name is missing');
        }

        return Namer::toLowercase('pum_tree_'.$this->context->getProjectName().'_'.$object->getName().'_'.$children_field);
    }

    protected function createNode($label, $parent)
    {
        $parentSetter   = $this->getParentSetter();
        $labelSetter    = $this->getLabelSetter();
        $em             = $this->getOEM();
        $repo           = $this->getRepository();
        $node           = $this->newChild();

        if (null !== $parent) {
            $parent = $repo->find($parent);
        }

        $node->$parentSetter($parent);
        $node->$labelSetter(urldecode($label));

        $em->persist($node);
        $em->flush();

        return new JsonResponse('OK');
    }

    protected function deleteNode($node_id)
    {
        $em   = $this->getOEM();
        $repo = $this->getRepository();

        if (null !== $node = $repo->find($node_id)) {
            $em->remove($node);
            $em->flush();

            return new JsonResponse('OK');
        }

        return new JsonResponse('ERROR');
    }

    protected function renameNode($node_id, $label)
    {
        $labelSetter  = $this->getLabelSetter();
        $em           = $this->getOEM();
        $repo         = $this->getRepository();

        if (null !== $node = $repo->find($node_id)) {
            $node->$labelSetter($label);
            $em->flush();

            return new JsonResponse('OK');
        }

        return new JsonResponse('ERROR');
    }

    protected function moveNode($node_id, $new_pos, $old_pos, $new_parent, $old_parent)
    {
        $parentSetter = $this->getParentSetter();
        $em           = $this->getOEM();
        $repo         = $this->getRepository();
        $new_parent   = ($new_parent == 'root') ? null : $new_parent;
        $old_parent   = ($old_parent == 'root') ? null : $old_parent;

        if (null !== $node = $repo->find($node_id)) {
            if (null !== $new_parent_node = $new_parent) {
                if (null === $new_parent_node = $repo->find($new_parent)) {
                    return new JsonResponse('ERROR');
                }
            }

            // Update sequence for the tree
            if ($new_parent != $old_parent) {
                $qb = $repo->createQueryBuilder('o');
                $qb
                    ->update()
                    ->set('o.treeSequence', 'o.treeSequence + 1')
                    ->andWhere('o.treeSequence >= :sequence')
                ;
                if (null === $new_parent) {
                    $qb
                        ->andWhere('o.'.$this->options['parent_field'].' IS NULL')
                        ->setParameters(array(
                            'sequence' => $new_pos,
                        ))
                    ;
                } else {
                    $qb
                        ->andWhere('o.'.$this->options['parent_field'].' = :parent')
                        ->setParameters(array(
                            'sequence' => $new_pos,
                            'parent' => $new_parent
                        ))
                    ;
                }
                $qb
                    ->getQuery()
                    ->execute()
                ;

                $qb = $repo->createQueryBuilder('o');
                $qb
                    ->update()
                    ->set('o.treeSequence', 'o.treeSequence - 1')
                    ->andWhere('o.treeSequence > :sequence')
                ;
                if (null === $old_parent) {
                    $qb
                        ->andWhere('o.'.$this->options['parent_field'].' IS NULL')
                        ->setParameters(array(
                            'sequence' => $old_pos,
                        ))
                    ;
                } else {
                    $qb
                        ->andWhere('o.'.$this->options['parent_field'].' = :parent')
                        ->setParameters(array(
                            'sequence' => $old_pos,
                            'parent' => $old_parent
                        ))
                    ;
                }
                $qb
                    ->getQuery()
                    ->execute()
                ;

                $node->$parentSetter($new_parent_node);
            } else {
                $qb = $repo->createQueryBuilder('o');
                $qb
                    ->update()
                ;

                if ($new_pos > $old_pos) {
                    $qb
                        ->set('o.treeSequence', 'o.treeSequence - 1')
                        ->andWhere('o.treeSequence <= :new_pos')
                        ->andWhere('o.treeSequence > :old_pos')
                        ->setParameters(array(
                            'new_pos' => $new_pos,
                            'old_pos' => $old_pos,
                        ))
                    ;
                } else {
                    $qb
                        ->set('o.treeSequence', 'o.treeSequence + 1')
                        ->andWhere('o.treeSequence >= :new_pos')
                        ->andWhere('o.treeSequence < :old_pos')
                        ->setParameters(array(
                            'new_pos' => $new_pos,
                            'old_pos' => $old_pos,
                        ))
                    ;
                }

                if (null === $new_parent) {
                    $qb
                        ->andWhere('o.'.$this->options['parent_field'].' IS NULL')
                    ;
                } else {
                    $qb
                        ->andWhere('o.'.$this->options['parent_field'].' = :parent')
                        ->setParameters(array(
                            'parent' => $new_parent
                        ))
                    ;
                }
                $qb
                    ->getQuery()
                    ->execute()
                ;
            }

            $node->setTreeSequence($new_pos);
            $em->flush();

            return new JsonResponse('OK');
        }

        return new JsonResponse('ERROR');
    }

    protected function getRoots()
    {
        $rootNode = new TreeNode($id = 'root', $label = ucfirst($this->object->getName()), $icon = 'folder', $type = 'root', $isRoot = true);
        $rootNode = $this->populateNode($rootNode, $detail = true);

        return new JsonResponse($rootNode->toArray());
    }

    protected function getNode($id)
    {
        $nodes       = $this->getNodes();
        $label_field = $this->getLabelGetter();

        if (!$id || null === $object = $this->getRepository()->find($id)) {
            return new JsonResponse('ERROR');
        }

        $treeNode = new TreeNode($id, $object->$label_field());
        $treeNode = $this->populateNode($treeNode, $detail = true);

        return new JsonResponse($treeNode->toArray());
    }

    protected function populateNode(TreeNode $treeNode, $detail)
    {
        $nodes          = $this->getNodes();
        $parent_field   = $this->options['parent_field'];
        $em             = $this->getOEM();
        $repo           = $this->getRepository();

        $treeNode->setChildrenDetail($detail);
        $treeNode->setIcon('pumicon pumicon-' . $this->object->getTree()->getIcon());

        if (!$treeNode->isRoot()) {
            $route = 'pa_object_edit';
            if (!$this->context->getContainer()->get('security.context')->isGranted('PUM_OBJ_EDIT', array(
                'project' => $this->context->getProjectName(),
                'beam' => $this->object->getBeam()->getName(),
                'object' => $this->object->getName()
                ))) {
                $route = 'pa_object_view';
            }

            $treeNode->setAAttrs(array(
                'class'            => 'yaah-js',
                'data-ya-target'   => '#pumAjaxModal .modal-content',
                'data-ya-location' => 'inner',
                'data-ya-href'     => $this->urlGenerator->generate($route, $parameters = array(
                    'beamName'  => $this->object->getBeam()->getName(),
                    'name'      => $this->object->getName(),
                    'id'        => $treeNode->getId(),
                    'mode'      => 'tree',
                )),
                'data-toggle' => 'modal',
                'data-target' => '#pumAjaxModal',
                'data-toggle' => 'modal',
            ));
        }

        if ($detail) {
            $parent_id = $treeNode->getId() != 'root' ? $treeNode->getId() : null;
            foreach ($repo->getObjectsBy(array($parent_field => $parent_id), array('treeSequence' => 'asc'), null, null, true)->getQuery()->getArrayResult() as $object) {
                $nodeDetail = in_array($object['id'], $nodes);
                $childNode  = new TreeNode($object['id'], $object[Namer::toCamelCase($this->options['label_field'])]);
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

    protected function getNodes()
    {
        $values = json_decode($this->request->cookies->get($this->getTreeNamespace()));

        if (is_array($values)) {
            return $values;
        }

        return array();
    }

    protected function addNode($node_id)
    {
        $values = $this->getNodes();

        if (!$node_id || in_array($node_id, $values)) {
            return new JsonResponse('ERROR');
        }

        $values[] = $node_id;

        return $this->storeCookie($values);
    }

    protected function removeNode($node_id)
    {
        $values = $this->getNodes();

        if (!$node_id || ($key = array_search($node_id, $values)) !== true) {
            return new JsonResponse('ERROR');
        }

        unset($values[$key]);

        return $this->storeCookie($values);
    }

    protected function storeCookie($values, $time = null)
    {
        if (null === $time) {
            $time = time() + 3600 * 24 * 365;
        }

        $cookie   = new Cookie($this->getTreeNamespace(), json_encode($values), $time);
        $response = new Response();

        $response->headers->setCookie($cookie);

        return $response->send();
    }

    protected function clearCookie()
    {
        $response = new Response();
        $response->headers->clearCookie($this->getTreeNamespace());

        return $response->send();
    }

    protected function getOpenedNodes()
    {
        $values = $this->getNodes();

        return new JsonResponse($values);
    }

    protected function getOEM()
    {
        return $this->context->getProjectOEM();
    }

    protected function getRepository()
    {
        if (null === $this->object) {
            throw new \RuntimeException('No object is defined');
        }

        return $this->getOEM()->getRepository($this->object->getName());
    }

    protected function newChild()
    {
        if (null === $this->object) {
            throw new \RuntimeException('No object is defined');
        }

        return $this->getOEM()->createObject($this->object->getName());
    }

    protected function getParentGetter()
    {
        return 'get'.ucfirst(Namer::toCamelCase($this->options['parent_field']));
    }

    protected function getParentSetter()
    {
        return 'set'.ucfirst(Namer::toCamelCase($this->options['parent_field']));
    }

    protected function getLabelGetter()
    {
        return 'get'.ucfirst(Namer::toCamelCase($this->options['label_field']));
    }

    protected function getLabelSetter()
    {
        return 'set'.ucfirst(Namer::toCamelCase($this->options['label_field']));
    }
}
