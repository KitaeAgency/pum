<?php

namespace Pum\Core\Extension\Search;

use Elasticsearch\Client;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Extension\Search\SearchableInterface;
use Pum\Core\Extension\Util\Namer;

class SearchEngine
{
    private $client;
    private $params;
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

    public function searchGlobal($text, $per_page=10, $page=1)
    {
        return
                $this
                    ->resetParams()
                    ->index()
                    ->size($per_page)
                    ->page($page)
                    ->match($text)
                    ->execute()
                ;
    }

    public function search($objectName, $text, $per_page=10, $page=1)
    {
        return
                $this
                    ->resetParams()
                    ->index()
                    ->size($per_page)
                    ->page($page)
                    ->type(self::getTypeName($objectName))
                    ->match(array('_all' => $text))
                    ->execute()
                ;
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

    /* 
     *
     *Improve with http://groups.google.com/a/elasticsearch.com/group/users/browse_thread/thread/549fb5ede5df6ff4/0890e504cc13d486
     *
     */
    public function resetParams() {
        $this->params = array();

        return $this;
    }

    public function index($val = null) {
        if (null !== $val) {
            $this->params['index'] = $val;
        } else if (null !== $this->projectName) {
            $this->params['index'] = self::getIndexName($this->projectName);
        }

        return $this;
    }

    public function type($val) {
        $this->params['type'] = $val;

        return $this;
    }

    public function select($values) {
        $fields = (isset($this->params['body']['fields'])) ? $this->params['body']['fields'] : array();
        $this->params['body']['fields'] = array_merge($fields, (array)$values);

        return $this;
    }

    public function size($val) {
        $this->params['body']['size'] = intval($val);

        return $this;
    }

    public function from($val) {
        $this->params['body']['from'] = intval($val);

        return $this;
    }

    public function page($val) {
        $per_page = (isset($this->params['body']['size'])) ? $this->params['body']['size'] : 10;
        $this->params['body']['from'] = abs(intval($val - 1)) * $per_page;

        return $this;
    }

    public function match($val) {
        $this->params['body']['query']['filtered']['query']['query_string']['query'] = $val;

        return $this;
    }

    public function filter(array $values) {
        $filter = array();
        foreach ($values as $key => $value) {
            $filter['term'][$key] = $value;
        }

        $this->params['body']['query']['filtered']['filter'] = $filter;

        return $this;
    }

    public function json($json) {
        $this->params['body'] = $json;

        return $this;
    }

    public function execute($debug = false) {
        $results = $this->client->search($this->params);

        if ($debug) {
            echo '<pre>';
            var_dump($this->params);exit;
        }

        if (isset($results['error'])) {
            $resultsTab['error']   = $results['error'];
            $resultsTab['status']  = $results['status'];
        } else {
            $resultsTab['total']   = $results['hits']['total'];
            $resultsTab['timeout'] = $results['timed_out'];
            $resultsTab['items']   = array();

            $fields = (isset($this->params['body']['fields'])) ? 'fields' : '_source';
            foreach ($results['hits']['hits'] as $hit) {
                $resultsTab['items'][$hit['_type']][] = array_merge(array('id' => $hit['_id'], 'score' => $hit['_score']), $hit[$fields]);
            }
        }

        return $resultsTab;
    }
}
