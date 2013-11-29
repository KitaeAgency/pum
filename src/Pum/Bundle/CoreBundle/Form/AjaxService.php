<?php

namespace Pum\Bundle\CoreBundle\Form;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AjaxService
{
    private $twig;
    private $tpl;

    public function __construct(\Twig_Environment $twig, $tpl = 'pum://search/rows.html.twig')
    {
        $this->twig = $twig;
        $this->tpl  = $tpl;
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
        $type   = $config->getType()->getName();
        if ($type !== 'pum_object_entity') {
            $this->throw404(sprintf('Form at path "%s" is not a pum_object_entity, it\'s a "%s".', $list, $type));
        }

        $class = $config->getOption('class');
        $object = $class::PUM_OBJECT;
        $em = $config->getOption('em');
        $results = $em->getRepository($object)->getSearchResult($q);

        $tpl = $this->twig->loadTemplate($this->tpl);

        if (!$tpl->hasBlock($block = 'search_row_'.$object)) {
            throw new \RuntimeException('Block "%s" is missing from template "%s".', $block, $this->tpl);
        }

        $res = array_map(function ($result) use ($tpl, $block, $object) {
            return $tpl->renderBlock($block, array(
                'object' => $result,
                $object  => $result
            ));
        }, $results);

        return new Response(json_encode($res));
    }

    private function throw404($message)
    {
        throw new NotFoundHttpException($message);
    }
}
