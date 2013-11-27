<?php

namespace Pum\Core\Extension\Search;

use Elasticsearch\Client;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Extension\Search\SearchableInterface;
use Pum\Core\Extension\Util\Namer;

class SearchEngine
{
    const SEARCH_TYPE_DEFAULT = 'search';
    const SEARCH_TYPE_COUNT   = 'count';

    private $client;
    private $params;
    private $projectName;

    public function __construct(Client $client)
    {
        $this->client     = $client;
        $this->searchType = self::SEARCH_TYPE_DEFAULT;
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
                    ->matchAll($text)
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
                    ->matchAll($text)
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

    public function searchType($val) {
        $this->searchType = $val;

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
        $this->params['body']['query']['filtered']['query']  = $val;

        return $this;
    }

    public function matchAll($val) {
        $this->params['body']['query']['filtered']['query']['match'] = array('_all' => $val);

        return $this;
    }

    public function andFilter($field, $val, $operator = '=') {
        return $this->filter($field, $val, $operator, 'and');
    }

    public function orFilter($field, $val, $operator = '=') {
        return $this->filter($field, $val, $operator, 'or');
    }

    public function filter($field, $val, $operator = '=', $type = 'and') {
        $filterMatch = 'term';
        $val = trim($val);
        $binds = array(
            '>=' => 'gte',
            '<=' => 'lte',
            '>'  => 'gt',
            '<'  => 'lt',
        );

        if ('%' === $val{0} || '%' === $val{strlen($val)-1}) {
            $val = str_replace('%', '*', $val);
        } else if (in_array($operator, array_keys($binds))) {
            $filterMatch = 'range';
            $operator    = $binds[$operator];
        } else {
             foreach ($binds as $key => $operator) {
                if (0 === $pos = strpos($val, $key)) {
                    $val         = substr($val, strlen($key));
                    $filterMatch = 'range';
                    $operator    = $operator;
                    break;
                }
            }
        }

        $filterData = array();
        switch ($filterMatch) {
            case 'term':
                $filterData = array($field => $val);
                break;

            case 'range':
                $filterData = array($field => array($operator => $val));
                break;
        }

        switch ($filterMatch) {
            case 'range':
                $found = false;

                if (isset($this->params['body']['query']['filtered']['filter'][$type])) {
                    foreach ($this->params['body']['query']['filtered']['filter'][$type] as $key => $value) {
                        if (isset($value[$filterMatch][$field])) {
                            $this->params['body']['query']['filtered']['filter'][$type][$key][$filterMatch][$field] = array_merge($value[$filterMatch][$field], array($operator => $val));
                            $found = true;
                        }
                    }
                }

                if (true === $found) {
                    break;
                }

            case 'term':
                $this->params['body']['query']['filtered']['filter'][$type][] = array(
                    $filterMatch => $filterData
                );
                break;
        }

        return $this;
    }

    public function execute($debug = false) {
        $searchType = $this->searchType;
        $results = $this->client->$searchType($this->params);

        if ($debug) {
            echo '<pre>';
            var_dump($this->params);exit;
        }

        switch ($this->searchType) {
            case self::SEARCH_TYPE_COUNT:
                if (isset($results['error'])) {
                    $resultsTab['error']   = $results['error'];
                    $resultsTab['status']  = $results['status'];
                } else {
                    $resultsTab['count'] = $results['count'];
                }
                break;

            default:
                if (isset($results['error'])) {
                    $resultsTab['error']   = $results['error'];
                    $resultsTab['status']  = $results['status'];
                } else {
                    $resultsTab['count']   = $results['hits']['total'];
                    $resultsTab['timeout'] = $results['timed_out'];
                    $resultsTab['items']   = array();

                    $fields = (isset($this->params['body']['fields'])) ? 'fields' : '_source';
                    foreach ($results['hits']['hits'] as $hit) {
                        $resultsTab['items'][$hit['_type']][] = array_merge(array('id' => $hit['_id'], 'score' => $hit['_score']), $hit[$fields]);
                    }
                }
                break;
        }

        return $resultsTab;
    }
}
