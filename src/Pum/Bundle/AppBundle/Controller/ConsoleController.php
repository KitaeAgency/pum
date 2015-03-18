<?php

namespace Pum\Bundle\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Console\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class ConsoleController extends Controller
{
    /**
     * @Route(path="/woodwork/console", name="app_console")
     */
    public function listAction(Request $request)
    {
        $this->assertGranted('ROLE_APP_CONFIG');

        return $this->render('PumAppBundle:Console:index.html.twig', array(

            ));
    }

    /**
     * @Route(path="/woodwork/console/cache_clear", name="app_console_cache_clear")
     */
    public function clearCacheAction(Request $request)
    {
        $this->execute('php ../app/console cache:clear --env=prod --no-debug');
        $this->addSuccess($this->get('translator')->trans('cache.cleared', array(), 'pum'));

        return $this->redirect($this->generateUrl('app_console'));
    }

    /**
     * @Route(path="/woodwork/console/assets_install", name="app_console_assets_install")
     */
    public function assetsInstallAction(Request $request)
    {
        $this->execute('php ../app/console assets:install ../web --env=prod --no-debug --symlink');
        $this->addSuccess($this->get('translator')->trans('assets.installed', array(), 'pum'));

        return $this->redirect($this->generateUrl('app_console'));
    }

    /**
     * @Route(path="/woodwork/console/assetic_dump", name="app_console_assetic_dump")
     */
    public function asseticDumpAction(Request $request)
    {
        $this->execute('php ../app/console assetic:dump --env=prod --no-debug');
        $this->addSuccess($this->get('translator')->trans('assetic.dumped', array(), 'pum'));

        return $this->redirect($this->generateUrl('app_console'));
    }
}
