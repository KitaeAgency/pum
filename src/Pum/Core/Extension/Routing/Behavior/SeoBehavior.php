<?php

namespace Pum\Core\Extension\Routing\Behavior;

use Pum\Core\BehaviorInterface;
use Pum\Core\Context\ObjectBuildContext;

class SeoBehavior implements BehaviorInterface
{
    public function buildObject(ObjectBuildContext $context)
    {
        $cb = $context->getClassBuilder();
        $field = $context->getObject()->getSeoField();
        if (!$field) {
            return; // misconfigured
        }

        $getter = 'get'.ucfirst($field->getCamelCaseName());

        $cb->addImplements('Pum\Core\Extension\Routing\RoutableInterface');
        $cb->createMethod('getSeoKey', null, 'return \Pum\Core\Extension\Util\Namer::toSlug($this->'.$getter.'());');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'seo';
    }
}
