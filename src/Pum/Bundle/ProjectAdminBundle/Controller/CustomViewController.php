<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Pum\Core\Definition\Beam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pum\Bundle\ProjectAdminBundle\Entity\CustomView;

class CustomViewController extends Controller
{
    /**
     * @Route(path="/{_project}/customview", name="pa_custom_view_index")
     */
    public function customViewsListAction(Request $request)
    {
        $this->assertGranted('ROLE_PA_CUSTOM_VIEWS');

        $customView = new CustomView();
        $customView
            ->setProject($project = $this->get('pum.context')->getProject())
            ->setUser($this->getUser())
        ;

        $repository = $this->getCustomViewRepository();
        $form       = $this->createForm('pa_custom_view', $customView);

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $repository->save($customView);

            $this->addSuccess($this->get('translator')->trans('customview.created', array(), 'pum'));

            return $this->redirect($this->generateUrl('pa_custom_view_index'));
        }

        return $this->render('PumProjectAdminBundle:CustomView:index.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * Verifies permissions and return customview repository (or null if disabled).
     *
     * @return PermissionRepository
     */
    private function getCustomViewRepository()
    {
        $this->assertGranted('ROLE_PA_CUSTOM_VIEWS');

        if (!$this->container->has('pum.customview_repository')) {
            return null;
        }

        return $this->get('pum.customview_repository');
    }
}
