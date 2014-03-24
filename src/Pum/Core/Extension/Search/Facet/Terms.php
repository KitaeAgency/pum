<?php

namespace Pum\Core\Extension\Search\Facet;

use Elasticsearch\Client;

class Terms extends Facet
{
    private $size;
    private $order;
    private $all_terms     = false;
    private $exclude_terms = array();

    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    public function setOrder($order)
    {
        if (!in_array($order, array('count', 'term', 'reverse_count', 'reverse_term'))) {
            throw new \RuntimeException('Unknown facet terms order : %s', $order);
        }

        $this->order = $order;

        return $this;
    }

    public function setAllTerms()
    {
        $this->all_terms = true;

        return $this;
    }

    public function excludeTerms($term)
    {
        $this->exclude_terms = array_merge((array)$term, $this->exclude_terms);

        return $this;
    }

    public function getArray()
    {
        $facet['terms'] = array();

        if (null === $this->field) {
            throw new \RuntimeException('You must set field to the facet, null given');
        }

        $facet['terms']['field'] = $this->field;

        if (null !== $this->size) {
            $facet['terms']['size'] = (int)$this->size;
        }

        if (null !== $this->order) {
            $facet['terms']['order'] = $this->order;
        }

        if ($this->all_terms) {
            $facet['terms']['all_terms'] = true;
        }

        if (!empty($this->exclude_terms)) {
            $facet['terms']['exclude'] = $this->exclude_terms;
        }

        return $facet;
    }
}
