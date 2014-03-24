<?php

namespace Pum\Core\Extension\Search\Facet;

use Elasticsearch\Client;

class Facet
{
    protected $name;
    protected $field;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    public static function createFacet($name, $type)
    {
        switch ($type) {
            case 'terms':
                return new Terms($name);

            case 'range':
                return new Range($name);

            case 'histogram':
                return new Histogram($name);

            case 'date_histogram':
                return new DateHistogram($name);

            default:
                throw new \RuntimeException('Unknow facet type or unsupported type for now');
        }
    }
}
