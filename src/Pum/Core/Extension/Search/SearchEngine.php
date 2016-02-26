<?php

namespace Pum\Core\Extension\Search;

use Elasticsearch\Client;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Extension\Search\SearchableInterface;
use Pum\Core\Extension\Util\Namer;
use Pum\Core\Extension\Search\Facet\Facet;
use Pum\Core\Extension\Search\Query\Query;
use Pum\Core\Extension\Search\Highlight\Highlight;
use Symfony\Bridge\Monolog\Logger;

class SearchEngine
{
    private $client;
    private $projectName;

    public function __construct(Logger $logger, array $params = array())
    {
        $this->client = new Client($params);
        $this->logger = $logger;
    }

    public function setProjectName($projectName)
    {
        $this->projectName = $projectName;

        return $this;
    }

    public function createSearch()
    {
        $search = new Search($this->client, $this->logger);

        return $search->index(self::getIndexName($this->projectName));
    }

    public static function createQuery($type, $value = null)
    {
        return Query::createQuery($type, $value);
    }

    public static function createHighlight($fields)
    {
        return new Highlight($fields);
    }

    public static function createFacet($type, $name)
    {
        return Facet::createFacet($type, $name);
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

    public function search($objectName, $text, $per_page = 10, $page = 1)
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
        $settings = array();

        foreach ($object->getSearchFields() as $field) {
            $props[$field->getName()] = array(
                'type' => $field->getType(),
                'analyzer' => 'standard',
                'index' => $field->getIndex()
            );

            if (!empty($field->getSettings()) && is_array($field->getSettings())) {
                foreach ($field->getSettings() as $setting) {
                    switch ($setting['type']) {
                        case 'analyzer':
                            $props[$field->getName()]['analyzer'] = $setting['analyzer_name'];
                            $settings['analysis']['analyzer'][$setting['analyzer_name']] = array(
                                'type' => $setting['analyzer_type']
                            );

                            if ($setting['analyzer_stopwords'] == true) {
                                $settings['analysis']['analyzer'][$setting['analyzer_name']]['stopwords'] = explode(',', $setting['analyzer_stopwords_list']);
                            }
                            break;
                    }
                }
            }
        }

        $config = array(
            'index' => $indexName,
            'type'  => $typeName,
            'body'  => array(
                $typeName => array(
                    'properties' => $props,
                )
            )
        );

        try {
            $indices->close(array('index' => $indexName));

            if (!empty($settings)) {
                $indices->putSettings(array(
                    'index' => $indexName,
                    'body' => $settings
                ));
            }

            $indices->putMapping($config);
            $indices->open(array('index' => $indexName));
        } catch (\Exception $e) {
            $indices->open(array('index' => $indexName));
            throw $e;
        }
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

    public static function getIndexName($projectName)
    {
        return Namer::toLowercase('pum_index_'.$projectName);
    }

    public static function getTypeName($objectName)
    {
        return Namer::toLowercase($objectName);
    }
}
