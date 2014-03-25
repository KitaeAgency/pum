<?php

namespace Pum\Core\Extension\Search;

use Elasticsearch\Client;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Extension\Search\SearchableInterface;
use Pum\Core\Extension\Util\Namer;
use Pum\Core\Extension\Search\Facet\Facet;
use Pum\Core\Extension\Search\Query\Query;

class SearchEngine
{
    private $client;
    private $projectName;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function setProjectName($projectName)
    {
        $this->projectName = $projectName;

        return $this;
    }

    public function createSearch()
    {
        $search = new Search($this->client);

        return $search->index(self::getIndexName($this->projectName));
    }

    static public function createQuery($type, $value = null)
    {
        return Query::createQuery($type, $value);
    }

    static public function createFacet($name, $type)
    {
        return Facet::createFacet($name, $type);
    }

    public function searchGlobal($text, $per_page = 10, $page = 1)
    {
        return $this
            ->createSearch()
            ->perPage($per_page)
            ->page($page)
            ->match(array('_all' => $text))
            ->execute()
        ;
    }

    public function search($objectName, $text, $per_page=10, $page=1)
    {
        return $this
            ->createSearch()
            ->perPage($per_page)
            ->page($page)
            ->type(self::getTypeName($objectName))
            ->matchAll($text)
            ->execute()
        ;
    }

    public function createIndex($indexName)
    {
    }

    public function deleteIndex($indexName, $type)
    {
        $indices = $this->client->indices();

        if ($indices->exists(array('index' => $indexName))) {
            $indices->delete(array('index' => $indexName));
        }
    }

    public function updateIndex($indexName, $typeName, ObjectDefinition $object)
    {
        $indices = $this->client->indices();

        if (!$indices->exists(array('index' => $indexName))) {
            $indices->create(array('index' => $indexName));
        }

        if ($indices->existsType(array('index' => $indexName, 'type' => $typeName))) {
            $indices->deleteMapping(array('index' => $indexName, 'type' => $typeName));
        }

        $props = array();

        foreach ($object->getSearchFields() as $field) {
            $props[$field->getName()] = array(
                'type' => $field->getType(),
                'analyzer' => 'standard',
                'index' => $field->getIndex()
            );
        }

        $config = array(
            'index' => $indexName,
            'type'  => $typeName,
            'body'  => array($typeName => array('properties' => $props))
        );

        $indices->putMapping($config);
    }

    public function put(SearchableInterface $object)
    {
        $this->client->index(array(
            'body' => $object->getSearchValues(),
            'index' => $object->getSearchIndexName(),
            'type' => $object->getSearchTypeName(),
            'id' => $object->getId()
        ));
    }

    public function delete(SearchableInterface $object)
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
