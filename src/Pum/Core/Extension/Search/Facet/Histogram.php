<?php

namespace Pum\Core\Extension\Search\Facet;

use Elasticsearch\Client;

class Histogram extends Facet
{
    const FACET_KEY = 'histogram';

    private $interval;

    public function setInterval($interval)
    {
        $this->interval = $interval;

        return $this;
    }

    public function getArray()
    {
        if (null === $this->field) {
            throw new \RuntimeException('You must set field to the facet, null given');
        }

        $facet['field'] = $this->field;

        if (null !== $this->interval) {
            $facet['interval'] = $this->interval;
        }

        return array($this::FACET_KEY => $facet);
    }
}
