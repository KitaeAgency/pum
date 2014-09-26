<?php

namespace Pum\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Controller extends BaseController
{
    # shortcut to get context
    public function getContext()
    {
        return $this->get('pum.context');
    }

    # shortcut to get project
    public function getProject()
    {
        return $this->getContext()->getProject();
    }

    # shortcut to get objectDefinition
    public function getObjectDefinition($objectName)
    {
        return $this->getProject()->getObject($objectName);
    }

    # shortcut to get object entity manager
    public function getOEM()
    {
        return $this->getContext()->getProjectOEM();
    }

    # shortcut to get repository on pum object
    public function getRepository($objectName)
    {
        return $this->getOEM()->getRepository($objectName);
    }

    # shortcut to get object clazs
    public function getClassName($objectName)
    {
        return $this->get('pum')->getClassName($this->getContext()->getProjectName(), $objectName);
    }

    # shortcut to create pum object
    public function createObject($objectName)
    {
        return $this->getOEM()->createObject($objectName);
    }

    public function persist()
    {
        $objects = func_get_args();
        foreach ($objects as $object) {
            $this->getOEM()->persist($object);
        }

        return $this->getOEM();
    }

    public function remove()
    {
        $objects = func_get_args();
        foreach ($objects as $object) {
            $this->getOEM()->remove($object);
        }

        return $this->getOEM();
    }

    public function flush()
    {
        return $this->getOEM()->flush();
    }

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

    public function throwNotFound($message = 'Not found')
    {
        throw $this->createNotFoundException($message);
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

    public function throwAccessDenied($message = 'Forbidden')
    {
        throw new AccessDeniedException($message);
    }
}
