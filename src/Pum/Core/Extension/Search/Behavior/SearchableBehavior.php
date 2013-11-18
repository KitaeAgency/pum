<?php

namespace Pum\Core\Extension\Search\Behavior;

use Pum\Core\BehaviorInterface;
use Pum\Core\Context\ObjectBuildContext;
use Pum\Core\Extension\Util\Namer;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class SearchableBehavior implements BehaviorInterface
{
    public function buildObject(ObjectBuildContext $context)
    {
        $cb = $context->getClassBuilder();
        $searchFields = $context->getObject()->getSearchFields();

        $getValuesBody = 'return array(';
        $getWeightsBody = 'return array(';
        $language = new ExpressionLanguage();
        foreach ($searchFields as $searchField) {
            $name = Namer::toLowercase($searchField->getName());
            $getValuesBody.= "'$name' => ".$language->compile($searchField->getExpression(), array('this')).',';
            $getWeightsBody.= "'$name' => ".$searchField->getWeight().',';
        }
        $getValuesBody .= ');';
        $getWeightsBody .= ');';

        $indexName = Namer::toLowercase('pum_search_'.$context->getProject()->getName().'_'.$context->getObject()->getName());

        $cb->addImplements('Pum\Core\Extension\Search\SearchableInterface');
        $cb->createMethod('getSearchValues', null, $getValuesBody);
        $cb->createMethod('getSearchWeights', null, $getWeightsBody);
        $cb->createMethod('getSearchIndexName', null, 'return "'.$indexName.'";');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'seo';
    }
}
