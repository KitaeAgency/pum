<?php

namespace Pum\Core\Definition\View;

abstract class AbstractView
{
    /**
     * @var FormViewNode
     */
    protected $view;

    /**
     * @return FormViewNode
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @return Boolean
     */
    public function hasViewTab($nodeId)
    {
        if (null === $root = $this->getView()) {
            return false;
        }

        foreach ($root->getChildren() as $node) {
            if ($nodeId == $node->getId() && $node->isTab()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Integer or null
     */
    public function getDefaultViewTab()
    {
        if (null === $root = $this->getView()) {
            return null;
        }

        foreach ($root->getChildren() as $node) {
            if ($node->isTab()) {
                return $node->getId();
            }
        }

        return null;
    }

    /**
     * @return String
     */
    public function getDefaultViewTabType($nodeId)
    {
        switch ($this::VIEW_TYPE) {
            case 'formview':
                $method = 'getFormViewField';
                break;

            case 'objectview':
                $method = 'getObjectViewField';
                break;
            
            default:
                return;
        }

        if (null !== $root = $this->getView()) {
            foreach ($root->getChildren() as $node) {
                if ($nodeId == $node->getId() && $node->isTab() || null === $nodeId) {
                    foreach ($node->getChildren() as $child) {
                        switch ($child->getType()) {
                            case $child::TYPE_GROUP_FIELD:
                                return array('regularFields', null);
                            break;

                            case $child::TYPE_FIELD:
                                if (null !== $child->$method() && 'tab' == $child->$method()->getOption('form_type')) {
                                    return array('relationFields', $child->$method());
                                }

                                return array('regularFields', null);
                            break;
                        }

                        break;
                    }
                }
            }
        }

        return array('regularFields', null);
    }

        /**
     * @return Integer
     */
    public function countTabs()
    {
        if (null === $root = $this->getView()) {
            return 0;
        }

        $count = 0;

        foreach ($root->getChildren() as $node) {
            if ($node->isTab()) {
                $count++;
            }
        }

        return $count;
    }
}