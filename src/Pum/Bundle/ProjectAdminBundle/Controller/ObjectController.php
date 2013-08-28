<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Pum\Core\Definition\Beam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class ObjectController extends Controller
{
    const PAGINATION_VALUES        = "10-25-50-100-250-500-1000";
    const PAGINATION_DEFAULT_VALUE = 10;

    /**
     * @Route(path="/{_project}/{beamName}/{name}", name="pa_object_list")
     * @ParamConverter("beam", class="Beam")
     */
    public function listAction(Request $request, Beam $beam, $name)
    {
        $this->assertGranted('ROLE_PA_LIST');

        $page              = $request->query->get('page', 1);
        $per_page          = $request->query->get('per_page', self::PAGINATION_DEFAULT_VALUE);
        $pagination_values = explode('-', self::PAGINATION_VALUES);

        if (!in_array($per_page, $pagination_values)) {
            throw new \RuntimeException(sprintf('Unvalid pagination value "%s". Available: "%s".', $per_page, self::PAGINATION_VALUES));
        }

        $sort              = $request->query->get('sort', '');
        $order             = $request->query->get('order', '');

        $pager             = $this->get('pum.context')->getProjectOEM()->getRepository($name)->getPage($page, $per_page, $sort, $order);

        return $this->render('PumProjectAdminBundle:Object:list.html.twig', array(
            'beam'              => $beam,
            'object_definition' => $beam->getObject($name),
            'pager'             => $pager,
            'pagination_values' => $pagination_values
        ));
    }

    /**
     * @Route(path="/{_project}/{beamName}/{name}/create", name="pa_object_create")
     * @ParamConverter("beam", class="Beam")
     */
    public function createAction(Request $request, Beam $beam, $name)
    {
        $this->assertGranted('ROLE_PA_EDIT');

        $oem    = $this->get('pum.context')->getProjectOEM();
        $object = $oem->createObject($name);

        $form = $this->createForm('pum_object', $object)->add($this->get('form.factory')->create('submit'));

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $oem->persist($object);
            $oem->flush();
            $this->addSuccess('Object created');

            return $this->redirect($this->generateUrl('pa_object_edit', array('beamName' => $beam->getName(), 'name' => $name, 'id' => $object->id)));
        }

        return $this->render('PumProjectAdminBundle:Object:create.html.twig', array(
            'beam'              => $beam,
            'object_definition' => $beam->getObject($name),
            'form'              => $form->createView(),
            'object'            => $object,
        ));
    }

    /**
     * @Route(path="/{_project}/{beamName}/{name}/{id}/edit", name="pa_object_edit")
     * @ParamConverter("beam", class="Beam")
     */
    public function editAction(Request $request, Beam $beam, $name, $id)
    {
        $this->assertGranted('ROLE_PA_EDIT');

        $oem = $this->get('pum.context')->getProjectOEM();
        $repository = $oem->getRepository($name);
        $this->throwNotFoundUnless($object = $repository->find($id));
        $objectView = clone $object;

        $form = $this->createForm('pum_object', $object)->add($this->get('form.factory')->create('submit'));

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $oem->persist($object);
            $oem->flush();
            $this->addSuccess('Object updated');

            return $this->redirect($this->generateUrl('pa_object_edit', array('beamName' => $beam->getName(), 'name' => $name, 'id' => $id)));
        }

        return $this->render('PumProjectAdminBundle:Object:edit.html.twig', array(
            'beam'              => $beam,
            'object_definition' => $beam->getObject($name),
            'form'              => $form->createView(),
            'object'            => $objectView,
        ));
    }

    /**
     * @Route(path="/{_project}/{beamName}/{name}/{id}/delete", name="pa_object_delete")
     * @ParamConverter("beam", class="Beam")
     */
    public function deleteAction(Request $request, Beam $beam, $name, $id)
    {
        $this->assertGranted('ROLE_PA_DELETE');

        $oem = $this->get('pum.context')->getProjectOEM();
        $repository = $oem->getRepository($name);
        $this->throwNotFoundUnless($object = $repository->find($id));

        $oem->remove($object);
        $oem->flush();
        $this->addSuccess('Object deleted');

        return $this->redirect($this->generateUrl('pa_object_list', array('beamName' => $beam->getName(), 'name' => $name)));
    }
}
