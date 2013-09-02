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

        $config = $this->get('pum.config')->all();

        $page              = $request->query->get('page', 1);
        $per_page          = $request->query->get('per_page', $defaultPagination = ($config['pa_default_pagination']) ?: 10);
        $pagination_values = array_merge((array)$defaultPagination, $config['pa_pagination_values']);
        asort($pagination_values);
        $sort              = $request->query->get('sort', '');
        $order             = $request->query->get('order', '');

        if (!in_array($per_page, $pagination_values)) {
            throw new \RuntimeException(sprintf('Unvalid pagination value "%s". Available: "%s".', $per_page, implode('-', $pagination_values)));
        }

        return $this->render('PumProjectAdminBundle:Object:list.html.twig', array(
            'beam'              => $beam,
            'object_definition' => $beam->getObject($name),
            'pager'             => $this->get('pum.context')->getProjectOEM()->getRepository($name)->getPage($page, $per_page, $sort, $order),
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

        $form = $this->createForm('pum_object', $object);

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

        $form = $this->createForm('pum_object', $object);

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

    /**
     * @Route(path="/{_project}/{beamName}/{name}/deletelist", name="pa_object_delete_list")
     * @ParamConverter("beam", class="Beam")
     */
    public function deleteListAction(Request $request, Beam $beam, $name)
    {
        $this->assertGranted('ROLE_PA_DELETE');
        
        if ($request->request->has('entities')) {
            $oem = $this->get('pum.context')->getProjectOEM();
            $repository = $oem->getRepository($name);

            foreach ($request->request->get('entities') as $id) {
                $this->throwNotFoundUnless($object = $repository->find($id));
                $oem->remove($object);
            }

            $oem->flush();
            $this->addSuccess('Objects deleted');
        }

        return $this->redirect($this->generateUrl('pa_object_list', array('beamName' => $beam->getName(), 'name' => $name)));
    }

    /**
     * @Route(path="/{_project}/{beamName}/{name}/{id}/clone", name="pa_object_clone")
     * @ParamConverter("beam", class="Beam")
     */
    public function cloneAction(Request $request, Beam $beam, $name, $id)
    {
        $this->assertGranted('ROLE_PA_EDIT');

        $oem = $this->get('pum.context')->getProjectOEM();
        $repository = $oem->getRepository($name);
        $this->throwNotFoundUnless($object = $repository->find($id));
        $objectView = clone $object;
        
        if ($request->isMethod('POST')) {
            $newObject = $oem->createObject($name);
            $form = $this->createForm('pum_object', $newObject);
            if ($form->bind($request)->isValid()) {
                $oem->persist($newObject);
                $oem->flush();
                $this->addSuccess('Object updated');

                return $this->redirect($this->generateUrl('pa_object_list', array('beamName' => $beam->getName(), 'name' => $name)));
            }
        } else {
            $form = $this->createForm('pum_object', $object);
        }

        return $this->render('PumProjectAdminBundle:Object:clone.html.twig', array(
            'beam'              => $beam,
            'object_definition' => $beam->getObject($name),
            'form'              => $form->createView(),
            'object'            => $objectView,
        ));
    }
}
