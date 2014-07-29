<?php

namespace Pum\Bundle\CoreBundle\Form;

use Pum\Core\Extension\View\View;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        /*if (!$request->isXmlHttpRequest()) {
            //return;
        }*/

        if (!$request->query->has('_pum_list')) {
            return;
        }

        $list       = $request->query->get('_pum_list');
        $q          = $request->query->get('_pum_q');
        $field      = $request->query->get('_pum_field', null);
        $searchType = $request->query->get('_pum_q_type', 'normal');
        $delimiter  = $request->query->get('_pum_q_delimiter', '-');

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
                $qb = $config->getOption('query_builder');
                break;
            }

            if ($type->getName() === 'pum_ajax_object_entity') {
                $qb = null;
                break;
            }

            $type = $type->getParent();
        }

        if (null === $type) {
            throw new \RuntimeException('Form type is not a pum_object_entity');
        }

        $class   = $config->getOption('class');
        $object  = $class::PUM_OBJECT;
        $em      = $config->getOption('em');

        if (null !== $qb) {
            $repo    = $em->getRepository($class);
            $qb = $qb($repo);
        }

        switch ($searchType) {
            case 'ids':
                $results = $em->getRepository($object)->getResultByIds($ids = $q, $qb, $delimiter);
                break;

            default:
                $results = $em->getRepository($object)->getSearchResult($q, $qb, $field);
                break;
        }

        if (null === $field) {

            $res = array_map(function ($result) {
                return array(
                    'id'    => $result->getId(),
                    'value' => $this->view->renderPumObject($result, 'search_row')
                );
            }, $results);

        } else {

            $res = array_map(function ($result) use ($field, $object) {
                $getter = 'get'.ucfirst($field);

                switch ($field) {
                    case 'id':
                        return array(
                            'id'    => $result->getId(),
                            'value' => (string) (ucfirst($object).' #'.$result->$getter())
                        );
                        break;

                    default:
                        return array(
                            'id'    => $result->getId(),
                            'value' => (string) ($result->$getter().' #'.$result->getId())
                        );
                        break;
                }
                
            }, $results);

        }

        return new JsonResponse($res);
    }

    private function throw404($message)
    {
        throw new NotFoundHttpException($message);
    }
}
