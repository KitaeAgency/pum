<?php

namespace Pum\Core\Extension\Search\Facet;

use Elasticsearch\Client;

class Range extends Facet
{
    const FACET_KEY = 'range';
    const FROM      = 'from';
    const TO        = 'to';

    private $ranges;

    public function addRange(array $range)
    {
        foreach ($range as $k => $v) {
            if (in_array($k, array(self::FROM, self::TO))) {
                $this->ranges[] = $range;
            }
        }

        return $this;
    }

    public function getArray()
    {
        if (null === $this->field) {
            throw new \RuntimeException('You must set field to the facet, null given');
        }

        $facet['field'] = $this->field;

        if (!empty($this->ranges)) {
            foreach ($this->ranges as $range) {
                $facet['ranges'][] = $range;
            }
        }

        $array = array($this::FACET_KEY => $facet);

        if ($this->global) {
            $array['global'] = true;
        }

        return $array;
    }
}
