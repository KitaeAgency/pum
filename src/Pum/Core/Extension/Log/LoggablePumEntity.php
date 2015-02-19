<?php

namespace Pum\Core\Extension\Log;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class LoggablePumEntity extends LoggableEntity
{
    public function __construct($route, $parameters = array(), $origin = Log::NONE, $event = null)
    {
        parent::__construct(null, $route, $parameters, $origin, $event);
    }

    public function getParameters()
    {
        $entity = $this->getEntity();

        $parameters = array();
        foreach ($this->parameters as $key => $parameter) {
            $parameters[$key] = $this->expressionLanguage->evaluate(
                $parameter,
                array(
                    'this' => $entity,
                    'PUM_PROJECT' => $entity::PUM_PROJECT,
                    'PUM_BEAM' => $entity::PUM_BEAM,
                    'PUM_OBJECT' => $entity::PUM_OBJECT,
                )
            );
        }
        return $parameters;
    }

    public function getProject()
    {
        $entity = $this->getEntity();
        
        return $entity::PUM_PROJECT;
    }

    public function match($object, $event = null)
    {
        if (strpos(get_class($object), 'pum_obj_') !== false && ($this->event == $event || $this->event == null)) {
            $this->setEntity($object);
            return true;
        }

        $this->entity = null;
        return false;
    }
}
