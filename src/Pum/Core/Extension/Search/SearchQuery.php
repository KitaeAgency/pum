<?php

namespace Pum\Core\Extension\Search;

use Elasticsearch\Client;
use Pum\Core\Extension\Search\Result\Result;
use Pum\Core\Extension\Search\Facet\Facet;

class SearchQuery
{
    const DESC = 'desc';
    const ASC  = 'asc';

    private $client;

    private $index;
    private $type;

    private $query;

    private $perPage = 10;
    private $page    = 1;
    private $sorts   = array();

    private $fields = array();
    private $facets = array();

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return SearchQuery
     */
    public function index($index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * @return SearchQuery
     */
    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return SearchQuery
     */
    public function match($field, $value)
    {
        $filtered = preg_replace('/(^%|%$)/', '.*', $value);

        if ($value === $filtered) {
            $this->query['match'][$field] = $value;
        } else {
            $this->query['regexp'][$field] = $filtered;
        }

        return $this;
    }

    /**
     * @return SearchQuery
     */
    public function matchAll($text)
    {
        $this->match('_all', $text);

        return $this;
    }

    /**
     * @return SearchQuery
     */
    public function addSort($sort)
    {
        $sortby = $sort[key($sort)] = strtolower(reset($sort));

        if (!in_array($sortby, array(self::ASC, self::DESC))) {
            $sort[key($sort)] = self::ASC;
        }

        $this->sorts[] = $sort;

        return $this;
    }

    /**
     * @return SearchQuery
     */
    public function addFacet(Facet $facet)
    {
        $this->facets[] = $facet;

        return $this;
    }

    /**
     * @return SearchQuery
     */
    public function select($field)
    {
        $this->fields = array_merge((array)$field, $this->fields);

        return $this;
    }

    /**
     * @return SearchQuery
     */
    public function perPage($perPage)
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * @return SearchQuery
     */
    public function page($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return SearchResult
     */
    public function execute($debug = false)
    {
        if ($debug) {
            echo '<pre>';
            print_r($this->getQuery());
            exit;
        }

        return new Result($this->client->search($this->getQuery()));
    }

    public function count()
    {
        return $this->client->count($this->getQuery());
    }

    public function getQuery()
    {
        $query = array();

        if (null !== $this->index) {
            $query['index'] = $this->index;
        }

        if (null !== $this->type) {
            $query['type'] = $this->type;
        }

        $from = ($this->page - 1) * $this->perPage;
        $size = $this->perPage;

        $query['body']['from'] = $from;
        $query['body']['size'] = $size;

        if (empty($this->sorts)) {
            $query['body']['sort'][] = "_score";
        } else {
            foreach ($this->sorts as $sort) {
                $query['body']['sort'][] = $sort;
            }
        }

        if (!empty($this->fields)) {
             $query['body']['fields'] = $this->fields;
        }

        if (null !== $this->query) {
            $query['body']['query'] = $this->query;
        }

        if (!empty($this->facets)) {
            foreach ($this->facets as $facet) {
                $query['body']['facets'][$facet->getName()] = $facet->getArray();
            }
        }

        return $query;
    }
}


/*
    public function filter($field, $val, $operator = '=', $type = 'and') {
        $filterMatch = 'term';
        $val = trim($val);
        $binds = array(
            '>=' => 'gte',
            '<=' => 'lte',
            '>'  => 'gt',
            '<'  => 'lt',
        );

        if (in_array($operator, array_keys($binds))) {
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

    public function manualFilter($type, $data) {
        $this->params['body']['query']['filtered']['filter'][$type][] = $data;

        return $this;
    }

    public function sort($field, $type = "asc") {
        $this->params['body']['sort'][] = array($field => $type);

        return $this;
    }

    public function highlight($field, $options = array()) {
        $this->params['body']['highlight']['fields'][$field] = new \stdClass($options);

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
                        if (isset($hit['highlight'])) {
                            foreach ($hit['highlight'] as $k => $highlight) {
                                $hit['highlight'][$k] = $highlight[0];
                            }
                            $hit[$fields] = array_merge($hit[$fields], $hit['highlight']);
                        }
                        $resultsTab['items'][$hit['_type']][] = array_merge(array('id' => $hit['_id'], 'score' => $hit['_score']), $hit[$fields]);
                    }

                }
                break;
        }

        return $resultsTab;
    }
*/
