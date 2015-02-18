<?php

namespace Pum\Core\Extension\Log;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class LoggableEntity
{
    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var route
     */
    protected $route;

    /**
     * @var parameters
     */
    protected $parameters;

    /**
     * @var class
     */
    protected $class;

    /**
     * @var origin (woodwork / project-admin)
     */
    protected $origin;

    /**
     * @var event (insert / update / delete)
     */
    protected $event;

    /**
     * @var expressionLanguage
     */
    protected $expressionLanguage;

    public function __construct($class, $route, $parameters = array(), $origin = Log::NONE, $event = null)
    {
        $this->class = $class;
        $this->origin = $origin;
        $this->route = $route;
        $this->parameters = $parameters;
        $this->event = $event;

        $this->expressionLanguage = new ExpressionLanguage();

        $this->entity = null;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    protected function getEntity()
    {
        if ($this->entity == null) {
            throw new \RunTimeException("Entity is not set, did you forget to call setEntity before ?");
        }
        return $this->entity;
    }

    public function getOrigin()
    {
        return $this->origin;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getParameters()
    {
        $parameters = array();
        foreach ($this->parameters as $key => $parameter) {
            $parameters[$key] = $this->expressionLanguage->evaluate($parameter, array('this' => $this->getEntity()));
        }
        return $parameters;
    }

    public function getProject()
    {
        return null;
    }

    public function match($object, $event = null)
    {
        if ($this->class == get_class($object) && ($this->event == $event || $this->event == null)) {
            $this->setEntity($object);
            return true;
        }

        $this->entity = null;
        return false;
    }
}
