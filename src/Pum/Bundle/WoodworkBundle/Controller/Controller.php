<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Controller extends BaseController
{
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
