<?php

namespace Pum\Core\Extension\Search\Behavior;

use Pum\Core\BehaviorInterface;
use Pum\Core\Context\ObjectBuildContext;

class SearchableBehavior implements BehaviorInterface
{
    public function buildObject(ObjectBuildContext $context)
    {
        $cb = $context->getClassBuilder();
        $searchFields = $context->getObject()->getSearchFields();

        return; // XXX todo

        $getter = 'get'.ucfirst($field->getCamelCaseName());
        $tplExport = var_export($context->getObject()->getSeoTemplate(), true);
        $seoOrder = var_export($context->getObject()->getSeoOrder(), true);

        $cb->addImplements('Pum\Core\Extension\Routing\RoutableInterface');
        $cb->createMethod('getSeoKey', null, 'return \Pum\Core\Extension\Util\Namer::toSlug($this->'.$getter.'());');
        $cb->createMethod('getSeoTemplate', null, 'return '.$tplExport.';');
        $cb->createMethod('getSeoOrder', null, 'return '.$seoOrder.';');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'seo';
    }
}
