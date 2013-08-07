<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
{
    /**
     * @Route(path="/users", name="ww_user_list")
     */
    public function listAction()
    {
        if (!$this->container->has('pum.user_manager')) {
            return $this->render('PumWoodworkBundle:User:disabled.html.twig');
        }
    }
}
