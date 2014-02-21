<?php

namespace Pum\Bundle\CoreBundle\Form;

use Pum\Core\Extension\View\View;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AjaxService
{
    private $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function handleForm(FormInterface $form, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            //return;
        }

        if (!$request->query->has('_pum_list')) {
            return;
        }

        $list = $request->query->get('_pum_list');
        $q    = $request->query->get('_pum_q');

        $exp = explode('.', $list);
        foreach ($exp as $segment) {
            if (!$form->has($segment)) {
                $this->throw404('Invalid Ajax path: '.$list);
            }

            $form = $form->get($segment);
        }

        $config = $form->getConfig();
        $type   = $config->getType();
        while ($type) {
            if ($type->getName() === 'pum_object_entity') {
                break;
            }

            $type = $type->getParent();
        }

        if (null === $type) {
            throw new \RuntimeException('Form type is not a pum_object_entity');
        }

        $class = $config->getOption('class');
        $object = $class::PUM_OBJECT;
        $em = $config->getOption('em');
        $results = $em->getRepository($object)->getSearchResult($q);

        $res = array_map(function ($result) {
            return array(
                'id'    => $result->getId(),
                'value' => $this->view->renderPumObject($result, 'search_row')
            );
        }, $results);

        return new Response(json_encode($res));
    }

    private function throw404($message)
    {
        throw new NotFoundHttpException($message);
    }
}
