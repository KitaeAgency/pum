<?php

namespace Pum\Core\Extension\Search;

use Elasticsearch\Client;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Extension\Search\SearchableInterface;
use Pum\Core\Extension\Util\Namer;

class SearchEngine
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function search($projectName, $objectName, $text)
    {
        $params['index'] = self::getIndexName($projectName, $objectName);
        $params['type']  = 'pum';
        $params['body']['query']['match']['_all'] = $text;

        $results = $this->client->search($params);

        $result = array();

        foreach ($results['hits']['hits'] as $hit) {
            $result[] = array_merge(array('id' => $hit['_id']), $hit['_source']);
        }

        return $result;
    }

    public function updateIndex($indexName, ObjectDefinition $object)
    {
        $indices = $this->client->indices();

        if ($indices->exists(array('index' => $indexName))) {
            $indices->delete(array('index' => $indexName));
        }

        $props = array();

        foreach ($object->getSearchFields() as $field) {
            $props[$field->getName()] = array(
                'type' => 'string',
                'analyzer' => 'standard'
            );
        }

        $config = array(
            'index' => $indexName,
            'body' => array(
                'mappings' => array(
                    'pum' => array(
                        'properties' => $props
                    )
                )
            )
        );

        $indices->create($config);
    }

    public function index(SearchableInterface $object)
    {
        $this->client->index(array(
            'body' => $object->getSearchValues(),
            'index' => $object->getSearchIndexName(),
            'type' => 'pum',
            'id' => $object->getId()
        ));
    }

    public function desindex(SearchableInterface $object)
    {
        $this->client->delete(array(
            'index' => $object->getSearchIndexName(),
            'type' => 'pum',
            'id' => $object->getId()
        ));
    }

    static public function getIndexName($projectName, $objectName)
    {
        return Namer::toLowercase('pum_index_'.$projectName.'_'.$objectName);
    }
}
