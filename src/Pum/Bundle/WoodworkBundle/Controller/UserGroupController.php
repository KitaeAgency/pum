<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class UserGroupController extends Controller
{
    /**
     * @Route(path="/usergroups", name="ww_usergroup_list")
     */
    public function listAction(Request $request)
    {
        if ((!$groupRepository = $this->getGroupRepository()) || (!$userRepository = $this->getUserRepository())) {
            return $this->render('PumWoodworkBundle:User:disabled.html.twig');
        }

        return $this->render('PumWoodworkBundle:UserGroup:list.html.twig', array(
            'pager' => $groupRepository->getPage($request->query->get('page', 1))
        ));
    }

    /**
     * Verifies permissions and return group repository (or null if disabled).
     *
     * @return GroupRepository
     */
    private function getGroupRepository()
    {
        $this->assertGranted('ROLE_WW_USERS');

        if (!$this->container->has('pum.group_repository')) {
            return null;
        }

        return $this->get('pum.group_repository');
    }

    /**
     * Verifies permissions and return user repository (or null if disabled).
     *
     * @return UserRepository
     */
    private function getUserRepository()
    {
        $this->assertGranted('ROLE_WW_USERS');

        if (!$this->container->has('pum.user_repository')) {
            return null;
        }

        return $this->get('pum.user_repository');
    }
}
