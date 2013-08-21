<?php

namespace Pum\Bundle\TypeExtraBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    /**
     * @Route(path="/medias/{path}", name="te_media_view", defaults={"path" = null})
     */
    public function viewAction($path)
    {
        if ($path !== null) {
            $storage = $this->get('type_extra.media.storage.driver');
            $storage->getFile(base64_decode($path));
        }

        return new Response("", 404);
    }
}
