<?php

namespace Pum\Core\Extension\Search\Facet;

use Elasticsearch\Client;

class Facet
{
    protected $type;
    protected $name;
    protected $field;
    protected $global = false;

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

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    public function isGlobal()
    {
        $this->global = true;

        return $this;
    }

    public static function createFacet($name, $type)
    {
        switch ($type) {
            case 'terms':
                $obj = new Terms($name);
                break;

            case 'range':
                $obj = new Range($name);
                break;

            case 'histogram':
                $obj = new Histogram($name);
                break;

            case 'date_histogram':
                $obj = new DateHistogram($name);
                break;

            default:
                throw new \RuntimeException('Unknow facet type or unsupported type for now');
        }

        return $obj->setType($type);
    }
}
