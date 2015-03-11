<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\View;

use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\View\Formview;
use Pum\Core\Definition\View\FormViewNode;
use Pum\Core\Extension\Util\Namer;
use Symfony\Component\HttpFoundation\Request;

/**
 * A ForviewSchema.
 */
class FormViewSchema
{
    /**
     * @var ObjectDefinition
     */
    protected $objectDefinition;

    /**
     * @var Formview
     */
    protected $formview;

    /**
     * Constructor.
     */
    public function __construct(ObjectDefinition $objectDefinition)
    {
        $this->objectDefinition = $objectDefinition;
    }

    public function handleRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    public function isValid()
    {
        if (null === $this->request) {
            throw new \RuntimeException('Form is not submitted');
        }

        // TODO check if form is valid
        $isValid = true;

        return $isValid;
    }

    public function generateSchema()
    {
        $this->createFormView($name);

        return $this;
    }

    public function createFormView($name)
    {
        $this->formView = $this->objectDefinition->createFormView($name);

        return $this->formView;
    }

    public function createRootNode()
    {
        if (null === $this->request) {
            throw new \RuntimeException('Formview does not exist');
        }

        return $this->formView = $this->objectDefinition->createFormView($name)->createRootNodeView();

        return $this;
    }

    public function getRootNode()
    {
        if (null === $this->request) {
            throw new \RuntimeException('Formview does not exist');
        }

        return $this->formView->getView();
    }
}

// Use example
/*$fvs  = new FormViewSchema($objectDefinition);
$root = $fvs
    ->createFormView('Toto')
    ->createRootViewNode()
;
$root
    ->createNode('tab1', NodeView::TYPE_TAB, 1)
        ->createNode('group1', NodeView::TYPE_GROUP, 1)
            ->createNode('field1', NodeView::TYPE_FIELD, 1, $formViewField)->end()
            ->createNode('field2', NodeView::TYPE_FIELD, 1, $formViewField)->end()
        ->end()
        ->createNode('group2', NodeView::TYPE_GROUP, 2)
            ->createNode('field3', NodeView::TYPE_FIELD, 1, $formViewField)->end()
            ->createNode('field4', NodeView::TYPE_FIELD, 1, $formViewField)->end()
        ->end()
    ->end()
    ->createNode('tab2', NodeView::TYPE_TAB, 1)
        ->createNode('group3', NodeView::TYPE_GROUP, 1)
            ->createNode('field5', NodeView::TYPE_FIELD, 1, $formViewField)->end()
            ->createNode('field6', NodeView::TYPE_FIELD, 1, $formViewField)->end()
        ->end()
        ->createNode('group4', NodeView::TYPE_GROUP, 2)
            ->createNode('field7', NodeView::TYPE_FIELD, 1, $formViewField)->end()
            ->createNode('field8', NodeView::TYPE_FIELD, 1, $formViewField)->end()
        ->end()
    ->end()
;
*/
