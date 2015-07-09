<?php

namespace Pum\Core\Extension\Search\Behavior;

use Pum\Core\Behavior;
use Pum\Core\BehaviorInterface;
use Pum\Core\Context\ObjectBuildContext;
use Pum\Core\Context\ObjectContext;
use Pum\Core\Extension\Search\SearchEngine;
use Pum\Core\Extension\Util\Namer;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

class SearchableBehavior extends Behavior
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            $builder->create('searchable', 'section')
            ->add('searchable', 'ww_object_definition_searchable', array(
                'label' => ' ',
                'attr' => array(
                    'class' => 'pum-scheme-panel-sanguine'
                )
            ))
        );
    }

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

        $indexName = SearchEngine::getIndexName($context->getProject()->getName());
        $typeName = SearchEngine::getTypeName($context->getObject()->getName());

        $cb->addImplements('Pum\Core\Extension\Search\SearchableInterface');
        $cb->createMethod('getSearchValues', null, $getValuesBody);
        $cb->createMethod('getSearchWeights', null, $getWeightsBody);
        $cb->createMethod('getSearchIndexName', null, 'return "'.$indexName.'";');
        $cb->createMethod('getSearchTypeName', null, 'return "'.$typeName.'";');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'searchable';
    }
}
