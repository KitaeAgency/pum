<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Controller extends BaseController
{
    public function addSuccess($message)
    {
        $session = $this->get('request')->getSession();
        if (!$session) {
            return;
        }

        $session->getFlashBag()->add('message_success', $message);
    }

    public function addWarning($message)
    {
        $session = $this->get('request')->getSession();
        if (!$session) {
            return;
        }

        $session->getFlashBag()->add('message_warning', $message);
    }

    public function addInfo($message)
    {
        $session = $this->get('request')->getSession();
        if (!$session) {
            return;
        }

        $session->getFlashBag()->add('message_info', $message);
    }

    public function addError($message)
    {
        $session = $this->get('request')->getSession();
        if (!$session) {
            return;
        }

        $session->getFlashBag()->add('message_error', $message);
    }

    public function assertGranted($attributes, $subject = null)
    {
        if (!$this->get('security.context')->isGranted($attributes, $subject)) {
            throw new AccessDeniedException(sprintf('You don\'t have permission %s, required to access this resource.', json_encode($attributes)));
        }
    }

    public function throwNotFoundUnless($condition, $message = 'Not found')
    {
        if (!$condition) {
            throw $this->createNotFoundException($message);
        }
    }

    public function throwNotFoundIf($condition, $message = 'Not found')
    {
        if ($condition) {
            throw $this->createNotFoundException($message);
        }
    }
}
