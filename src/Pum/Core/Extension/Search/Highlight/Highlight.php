<?php

namespace Pum\Core\Extension\Search\Highlight;

class Highlight
{
    const KEY = 'highlight';

    private $fields      = array();
    private $pre_tags    = array();
    private $post_tags   = array();
    private $tags_schema = null;

    public function __construct($fields = array())
    {
        $this->addFields($fields);
    }

    public function addFields($fields)
    {
        foreach ((array)$fields as $field) {
            $this->addField($field);
        }

        return $this;
    }

    public function addField($field, $options = array())
    {
        $options = empty($options) ? new \stdClass() : $options;

        $this->fields[$field] = $options;

        return $this;
    }

    public function addPreTag($tag)
    {
        $this->pre_tags[] = $tag;

        return $this;
    }

    public function addPreTags($tags)
    {
        $this->pre_tags = array_merge((array)$tags, $this->pre_tags);

        return $this;
    }

    public function addPostTag($tag)
    {
        $this->post_tags[] = $tag;

        return $this;
    }

    public function addPostTags($tags)
    {
        $this->post_tags = array_merge((array)$tags, $this->post_tags);

        return $this;
    }

    public function setTagsSchema($tags_schema)
    {
        $this->tags_schema = $tags_schema;

        return $this;
    }

    public function getArray()
    {
        if (empty($this->fields)) {
            throw new \RuntimeException('You must set at least one field to the query, null given');
        }

        $result = array('fields' => $this->fields);

        if (!empty($pre_tag) && !empty($post_tags)) {
            $result['pre_tags']  = $pre_tags;
            $result['post_tags'] = $post_tags;
        } elseif (null !== $this->tags_schema) {
            $result['tags_schema'] = $this->tags_schema;
        }

        return $result;
    }
}
