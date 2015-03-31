<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Pum\Core\Definition\Project;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Symfony\Component\HttpFoundation\Request;
use Pum\Bundle\AppBundle\Entity\Group;

class GroupController extends Controller
{
    /**
     * @Route(path="/groups/{id}/edit", name="ww_group_edit")
     */
    public function editAction(Request $request, $id)
    {
        $this->assertGranted('ROLE_WW_USERS');

        if (!$repository = $this->getGroupRepository()) {
            return $this->render('PumWoodworkBundle:User:disabled.html.twig');
        }

        $this->throwNotFoundUnless($group = $repository->find($id));
        $form = $this->createForm('pum_group', $group, array('user' => $this->getUser()));
        $groupView = clone $group;

        if ($group->isAdmin() && !$this->getUser()->isAdmin()) {
            throw new \RuntimeException('You are not authorized to edit the super admin group');
        }

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $repository->save($group);
            $this->addSuccess($this->get('translator')->trans('ww.users.groups.update_success', array('%group%' => $group->getName()), 'pum'));

            $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken(
                $this->getUser(),
                null,
                'pum',
                $this->getUser()->getRoles()
            );

            $this->container->get('security.context')->setToken($token);

            return $this->redirect($this->generateUrl('ww_usergroup_list'));
        }

        return $this->render('PumWoodworkBundle:Group:edit.html.twig', array(
            'form' => $form->createView(),
            'group' => $groupView,
            'pager' => $repository->getPage()
        ));
    }

    /**
     * @Route(path="/groups/{id}/permissions", name="ww_group_permissions")
     */
    public function permissionsAction(Request $request, $id)
    {
        $this->assertGranted('ROLE_WW_USERS');

        if (!$repository = $this->getGroupRepository()) {
            return $this->render('PumWoodworkBundle:User:disabled.html.twig');
        }

        $this->throwNotFoundUnless($group = $repository->find($id));

        $ps = $this->get('pum.permission.schema');
        $ps
            ->setGroup($group)
            ->createSchema()
        ;

        if ($request->isMethod('POST') && $ps->handleRequest($request)->isValid()) {
            $ps->saveSchema();

            $this->addSuccess($this->get('translator')->trans('ww.users.groups.permissions.success', array('%group%' => $group->getAlias()), 'pum'));

            return $this->redirect($this->generateUrl('ww_group_permissions', array('id' => $group->getId())));
        }

        return $this->render('PumWoodworkBundle:Group:permissions.html.twig', array(
            'group'  => $group,
            'schema' => $ps->getSchema(),
            'error'  => $ps->getErrors(),
        ));
    }

    /**
     * @Route(path="/groups/{group}/permissions/tableviews/{project}/{beam}/{object}", name="ww_group_permissions_tableview")
     * @ParamConverter("project", class="Project", options={"projectName" = "project"})
     * @ParamConverter("beam", class="Beam", options={"beamName" = "beam"})
     * @ParamConverter("object", class="ObjectDefinition", options={"objectDefinitionName" = "object"})
     */
    public function permissionsTableViewsAction(Request $request, $group, Project $project, Beam $beam, ObjectDefinition $object)
    {
        $this->assertGranted('ROLE_WW_USERS');

        if (!$repository = $this->getGroupRepository()) {
            return $this->render('PumWoodworkBundle:User:disabled.html.twig');
        }

        $this->throwNotFoundUnless($group = $repository->find($group));

        $pts = $this->get('pum.permission.tableview.schema');
        $pts
            ->setGroup($group)
            ->setProject($project)
            ->setBeam($beam)
            ->setObjectDefinition($object)
            ->createSchema()
        ;

        if ($request->isMethod('POST') && $pts->handleRequest($request)->isValid()) {
            $pts->saveSchema();

            $this->addSuccess($this->get('translator')->trans('ww.users.groups.permissions.tableviews.success', array('%group%' => $group->getAlias()), 'pum'));
        }

        return $this->render('PumWoodworkBundle:Group:tableviews.html.twig', array(
            'group'  => $group,
            'object' => $object,
            'schema' => $pts->getSchema(),
            'errors'  => $pts->getErrors(),
        ));
    }

    /**
     * @Route(path="/groups/create", name="ww_group_create")
     */
    public function createAction(Request $request)
    {
        $this->assertGranted('ROLE_WW_USERS');

        if (!$repository = $this->getGroupRepository()) {
            return $this->render('PumWoodworkBundle:Group:disabled.html.twig');
        }

        $form = $this->createForm('pum_group', null, array('user' => $this->getUser()));

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $repository->save($group = $form->getData());
            $this->addSuccess($this->get('translator')->trans('ww.users.groups.add_success', array('%group%' => $group->getName()), 'pum'));

            return $this->redirect($this->generateUrl('ww_usergroup_list'));
        }

        return $this->render('PumWoodworkBundle:Group:create.html.twig', array(
            'form' => $form->createView(),
            'pager' => $repository->getPage()
        ));
    }

    /**
     * @Route(path="/groups/{id}/delete", name="ww_group_delete")
     */
    public function deleteAction($id)
    {
        $this->assertGranted('ROLE_WW_USERS');

        if (!$repository = $this->getGroupRepository()) {
            return $this->render('PumWoodworkBundle:Group:disabled.html.twig');
        }

        $this->throwNotFoundUnless($group = $repository->find($id));

        if ($group->isAdmin()) {
            throw new \RuntimeException(sprintf('Super admin group cannot be deleted'));
        } elseif ($this->getUser()->getGroup() === $group) {
            throw new \RuntimeException(sprintf('You cannot delete your own group'));
        }

        $repository->delete($group);
        $this->addSuccess($this->get('translator')->trans('ww.users.groups.delete_success', array('%group%' => $group->getName()), 'pum'));

        return $this->redirect($this->generateUrl('ww_usergroup_list'));
    }

    /**
     * Verifies permissions and return group repository (or null if disabled).
     *
     * @return GroupRepository
     */
    private function getGroupRepository()
    {
        if (!$this->container->has('pum.group_repository')) {
            return null;
        }

        return $this->get('pum.group_repository');
    }
}
