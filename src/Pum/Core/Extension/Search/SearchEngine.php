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

    public function searchGlobal($projectName, $text)
    {
        $params['index'] = self::getIndexName($projectName);
        $params['body']['query']['match']['_all'] = $text;

        $results = $this->client->search($params);

        $result = array();

        foreach ($results['hits']['hits'] as $hit) {
            $result[$hit['_type']][] = array_merge(array('id' => $hit['_id']), $hit['_source']);
        }

        return $result;
    }

    public function search($projectName, $objectName, $text)
    {
        $params['index'] = self::getIndexName($projectName);
        $params['type']  = self::getTypeName($objectName);
        $params['body']['query']['match']['_all'] = $text;

        $results = $this->client->search($params);

        $result = array();

        foreach ($results['hits']['hits'] as $hit) {
            $result[] = array_merge(array('id' => $hit['_id']), $hit['_source']);
        }

        return $result;
    }

    public function updateIndex($indexName, $typeName, ObjectDefinition $object)
    {
        $indices = $this->client->indices();

        if ($indices->existsType(array('index' => $indexName, 'type' => $typeName))) {
            $indices->deleteMapping(array('index' => $indexName, 'type' => $typeName));
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
                    $typeName => array(
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
            'type' => $object->getSearchTypeName(),
            'id' => $object->getId()
        ));
    }

    public function desindex(SearchableInterface $object)
    {
        $this->client->delete(array(
            'index' => $object->getSearchIndexName(),
            'type' => $object->getSearchTypeName(),
            'id' => $object->getId()
        ));
    }

    static public function getIndexName($projectName)
    {
        return Namer::toLowercase('pum_index_'.$projectName);
    }

    static public function getTypeName($objectName)
    {
        return Namer::toLowercase($objectName);
    }
}
