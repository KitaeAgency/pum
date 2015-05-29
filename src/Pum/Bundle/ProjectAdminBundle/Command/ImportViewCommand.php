<?php

namespace Pum\Bundle\ProjectAdminBundle\Command;

use Pum\Bundle\CoreBundle\Console\OutputLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Definition\View\TableViewFilter;
use Pum\Core\Definition\View\TableViewSort;
use Pum\Core\Definition\View\TableViewField;
use Pum\Core\Definition\View\FormView;
use Pum\Core\Definition\View\FormViewField;
use Pum\Core\Definition\View\FormViewNode;
use Pum\Core\Definition\View\ObjectView;
use Pum\Core\Definition\View\ObjectViewField;
use Pum\Core\Definition\View\ObjectViewNode;
use Pum\Bundle\ProjectAdminBundle\Entity\CustomView;

class ImportViewCommand extends ContainerAwareCommand
{
    protected $cache = array();

    protected function configure()
    {
        $this
            ->setName('pum:view:import')
            ->setDescription('Import views from folder : Resources/pum/view')
            ->addArgument('version', InputArgument::OPTIONAL, 'v1 or v2', 'v2')
            ->addOption('detail', null, InputOption::VALUE_OPTIONAL, 'Show views import progression', true)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $folders   = array();
        $version   = $input->getArgument('version');

        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
            $reflection = new \ReflectionClass($class);
            if (is_dir($dir = dirname($reflection->getFilename()).'/Resources/pum/view')) {
                $folders[] = $dir;
            }
        }

        if ($version == 'v1') {
            $nbForm   = $this->generateFormViewActionV1($folders);
            $nbObject = $this->generateObjectViewActionV1($folders);
            $nbTable  = $this->generateTableViewActionV1($folders);
        } else {
            $nbForm   = $this->generateFormViewAction($folders);
            $nbObject = $this->generateObjectViewAction($folders);
            $nbTable  = $this->generateTableViewAction($folders);
        }

        if ($input->getOption('detail')) {
            $output->writeln(sprintf('Import success for ObjectView : <info>%s</info>', $nbObject));
            $output->writeln(sprintf('Import success for FormView : <info>%s</info>', $nbForm));
            $output->writeln(sprintf('Import success for TableView : <info>%s</info>', $nbTable));
        }
    }


    /******************************************************************************************
     *
     * CREATE FORMVIEW
     *
     ******************************************************************************************/
    private function generateFormViewActionV1($folders)
    {
        return $this->generateFormViewAction($folders, $createMethod = '_createFormViewV1');
    }

    private function generateFormViewAction($folders, $createMethod = '_createFormView')
    {
        $nb = 0;

        if (!empty($folders)) {
            $manager = $this->getContainer()->get('pum');

            $finder = new Finder();
            $finder->in($folders);
            $finder->files()->name('*.formview.xml');

            foreach ($finder as $file) {
                $xml = new \SimpleXMLElement($file->getContents());

                if ($beamName = (string)$xml->beam) {
                    if ($manager->hasBeam($beamName)) {
                        $beam = $manager->getBeam($beamName);

                        if (null !== $xml->objects->object) {
                            foreach ($xml->objects->object as $object) {
                                $objectName = (string)$object->name;

                                if ($beam->hasObject($objectName)) {
                                    $objectDefinition = $beam->getObject($objectName);

                                    if (null !== $object->formviews->formview) {
                                        $existed = false;

                                        foreach ($object->formviews->formview as $view) {
                                            if (true === $this->deleteExistedFormView($objectDefinition, $view)) {
                                                $existed = true;
                                            }
                                        }

                                        if ($existed) {
                                            $manager->saveBeam($beam);
                                        }

                                        foreach ($object->formviews->formview as $view) {
                                            $nb++;
                                            $this->$createMethod($objectDefinition, $view);
                                        }
                                    }
                                }
                            }
                        }
                        $manager->saveBeam($beam);
                    }
                }
            }
        }

        return $nb;
    }

    private function deleteExistedFormView(ObjectDefinition &$objectDefinition, $view)
    {
        $viewName = (string)$view->name;

        if ($objectDefinition->hasFormView($viewName)) {
            $objectDefinition->removeFormView($objectDefinition->getFormView($viewName));

            return true;
        }

        return false;
    }

    private function _createFormViewV1(ObjectDefinition &$objectDefinition, $view)
    {
        $viewName = (string)$view->name;
        $formView = $objectDefinition->createFormView($viewName);

        $formView->setPrivate($this->bool($view->private));

        if ($this->bool($view->default)) {
            $formView->setDefault(true);
        }

        if (isset($view->type)) {
            $type = constant('Pum\\Core\\Definition\\View\\FormView::TYPE_' . strtoupper($view->type));
            if ($type !== null) {
                $formView->setType($type);
            }
        }

        if ($view->columns->count()) {
            $this->createFormViewFieldWithNode($view->columns->column, $objectDefinition, $formView);
        }
    }

    private function _createFormView(ObjectDefinition &$objectDefinition, $view)
    {
        $viewName = (string)$view->name;
        $formView = $objectDefinition->createFormView($viewName);

        $formView->setPrivate($this->bool($view->private));

        if ($this->bool($view->default)) {
            $formView->setDefault(true);
        }

        if (isset($view->type)) {
            $type = constant('Pum\\Core\\Definition\\View\\FormView::TYPE_' . strtoupper($view->type));
            if ($type !== null) {
                $formView->setType($type);
            }
        }

        $rootNode = FormViewNode::create($name = 'ROOT', $type = FormViewNode::TYPE_ROOT, $position = 0);
        $rootNode = $this->setTemplate($rootNode, $view);
        $formView->setView($rootNode);

        switch (true) {
            case $view->tabs->count():
                $this->createFormViewTabNodes($view->tabs->tab, $objectDefinition, $formView, $rootNode);
                break;

            case $view->groups->count():
                $this->createFormViewGroupNodes($view->groups->group, $objectDefinition, $formView, $rootNode);
                break;

            case $view->columns->count():
                $this->createFormViewFieldWithNode($view->columns->column, $objectDefinition, $formView, $rootNode);
                break;
        }
    }

    private function createFormViewTabNodes($tabs, ObjectDefinition &$objectDefinition, FormView &$formView, FormViewNode &$parentNode)
    {
        if (!$tabs->count()) {
            return;
        }

        $pos = 1;

        foreach ($tabs as $tab) {
            $node = FormViewNode::create((string)$tab->name, $type = FormViewNode::TYPE_TAB, $pos++);
            $node
                ->setParent($parentNode)
            ;

            $node = $this->setTemplate($node, $tab->options);

            $parentNode->addChild($node);

            switch (true) {
                case $tab->groups->count():
                    $this->createFormViewGroupNodes($tab->groups->group, $objectDefinition, $formView, $node);
                    break;

                case $tab->columns->count():
                    $this->createFormViewFieldWithNode($tab->columns->column, $objectDefinition, $formView, $node);
                    break;
            }
        }
    }

    private function createFormViewGroupNodes($groups, ObjectDefinition &$objectDefinition, FormView &$formView, FormViewNode &$parentNode)
    {
        if (!$groups->count()) {
            return;
        }

        $pos = 1;

        foreach ($groups as $group) {
            $node = FormViewNode::create((string)$group->name, $type = FormViewNode::TYPE_GROUP_FIELD, $pos++);
            $node
                ->setParent($parentNode)
            ;

            $node = $this->setTemplate($node, $group->options);

            $parentNode->addChild($node);

            $this->createFormViewFieldWithNode($group->columns->column, $objectDefinition, $formView, $node);
        }
    }

    private function createFormViewFieldWithNode($columns, ObjectDefinition &$objectDefinition, FormView &$formView, FormViewNode &$parentNode = null)
    {
        if (!$columns->count()) {
            return;
        }

        $pos = 0;

        foreach ($columns as $column) {
            $pos++;
            $fieldName = (string)$column->field;

            if ($objectDefinition->hasField($fieldName)) {
                $formViewField = FormViewField::create((string)$column->name, $field = $objectDefinition->getField($fieldName), FormViewField::DEFAULT_VIEW, $pos, (string)$column->placeholder, (string)$column->help, $this->bool($column->disabled));

                switch ($field->getType()) {
                    case FieldDefinition::RELATION_TYPE:
                        $options = $column->options;
                        $formViewField
                            ->setOption('form_type', $this->formtype($options->form_type))
                            ->setOption('property', $this->textField($objectDefinition, $field, (string)$options->property))
                            ->setOption('tableview', (string)$options->tableview)
                            ->setOption('allow_add', $this->bool($options->allow_add))
                            ->setOption('allow_select', $this->bool($options->allow_select))
                            ->setOption('allow_delete', $this->bool($options->allow_delete))
                        ;
                        if ((string)$options->force_type) {
                            $formViewField->setOption('force_type', (string)$options->force_type);
                        }
                        break;
                    case 'choice':
                        $options = $column->options;
                        if ($this->bool($options->expanded)) {
                            $formViewField->setoption('expanded', true);
                        }
                        if ($this->bool($options->multiple)) {
                            $formViewField->setoption('multiple', true);
                        }
                        break;

                    case 'html':
                        $options = $column->options;
                        $formViewField->setOption('config_json', (string)$options->config_json);
                        break;
                }

                $formView->addField($formViewField);

                if ($parentNode) {
                    $node = FormViewNode::create((string)$column->name, $type = FormViewNode::TYPE_FIELD, $pos, $formViewField);
                    $node
                        ->setParent($parentNode)
                    ;

                    $node = $this->setTemplate($node, $column->options);

                    $parentNode->addChild($node);
                }
            }
        }
    }

    /******************************************************************************************
     *
     * CREATE OBJECTVIEW
     *
     ******************************************************************************************/
    private function generateObjectViewActionV1($folders)
    {
        return $this->generateObjectViewAction($folders, $createMethod = 'createObjectViewV1');
    }

    private function generateObjectViewAction($folders, $createMethod = 'createObjectView')
    {
        $nb = 0;

        if (!empty($folders)) {
            $manager = $this->getContainer()->get('pum');

            $finder = new Finder();
            $finder->in($folders);
            $finder->files()->name('*.objectview.xml');

            foreach ($finder as $file) {
                $xml = new \SimpleXMLElement($file->getContents());

                if ($beamName = (string)$xml->beam) {
                    if ($manager->hasBeam($beamName)) {
                        $beam = $manager->getBeam($beamName);

                        if (null !== $xml->objects->object) {
                            foreach ($xml->objects->object as $object) {
                                $objectName = (string)$object->name;

                                if ($beam->hasObject($objectName)) {
                                    $objectDefinition = $beam->getObject($objectName);

                                    if (null !== $object->objectviews->objectview) {
                                        $existed = false;

                                        foreach ($object->objectviews->objectview as $view) {
                                            if (true === $this->deleteExistedObjectView($objectDefinition, $view)) {
                                                $existed = true;
                                            }
                                        }

                                        if ($existed) {
                                            $manager->saveBeam($beam);
                                        }

                                        foreach ($object->objectviews->objectview as $view) {
                                            $nb++;
                                            $this->$createMethod($objectDefinition, $view);
                                        }
                                    }
                                }
                            }
                        }
                        $manager->saveBeam($beam);
                    }
                }
            }
        }

        return $nb;
    }

    private function deleteExistedObjectView(ObjectDefinition &$objectDefinition, $view)
    {
        $viewName = (string)$view->name;

        if ($objectDefinition->hasObjectView($viewName)) {
            $objectDefinition->removeObjectView($objectDefinition->getObjectView($viewName));

            return true;
        }

        return false;
    }

    private function createObjectViewV1(ObjectDefinition &$objectDefinition, $view)
    {
        $viewName   = (string)$view->name;
        $objectView = $objectDefinition->createObjectView($viewName);

        $objectView->setPrivate($this->bool($view->private));

        if ($this->bool($view->default)) {
            $objectView->setDefault(true);
        }

        if ($view->columns->count()) {
            $this->createObjectViewFieldWithNode($view->columns->column, $objectDefinition, $objectView);
        }
    }

    private function createObjectView(ObjectDefinition &$objectDefinition, $view)
    {
        $viewName = (string)$view->name;
        $objectView = $objectDefinition->createObjectView($viewName);

        $objectView->setPrivate($this->bool($view->private));

        if ($this->bool($view->default)) {
            $objectView->setDefault(true);
        }

        $rootNode = ObjectViewNode::create($name = 'ROOT', $type = ObjectViewNode::TYPE_ROOT, $position = 0);
        $rootNode = $this->setTemplate($rootNode, $view);
        $objectView->setView($rootNode);

        switch (true) {
            case $view->tabs->count():
                $this->createObjectViewTabNodes($view->tabs->tab, $objectDefinition, $objectView, $rootNode);
                break;

            case $view->groups->count():
                $this->createObjectViewGroupNodes($view->groups->group, $objectDefinition, $objectView, $rootNode);
                break;

            case $view->columns->count():
                $this->createObjectViewFieldWithNode($view->columns->column, $objectDefinition, $objectView, $rootNode);
                break;
        }
    }

    private function createObjectViewTabNodes($tabs, ObjectDefinition &$objectDefinition, ObjectView &$objectView, ObjectViewNode &$parentNode)
    {
        if (!$tabs->count()) {
            return;
        }

        $pos = 1;

        foreach ($tabs as $tab) {
            $node = ObjectViewNode::create((string)$tab->name, $type = ObjectViewNode::TYPE_TAB, $pos++);
            $node
                ->setParent($parentNode)
            ;

            $node = $this->setTemplate($node, $tab->options);

            $parentNode->addChild($node);

            switch (true) {
                case $tab->groups->count():
                    $this->createObjectViewGroupNodes($tab->groups->group, $objectDefinition, $objectView, $node);
                    break;

                case $tab->columns->count():
                    $this->createObjectViewFieldWithNode($tab->columns->column, $objectDefinition, $objectView, $node);
                    break;
            }
        }
    }

    private function createObjectViewGroupNodes($groups, ObjectDefinition &$objectDefinition, ObjectView &$objectView, ObjectViewNode &$parentNode)
    {
        if (!$groups->count()) {
            return;
        }

        $pos = 1;

        foreach ($groups as $group) {
            $node = ObjectViewNode::create((string)$group->name, $type = ObjectViewNode::TYPE_GROUP_FIELD, $pos++);
            $node
                ->setParent($parentNode)
            ;

            $node = $this->setTemplate($node, $group->options);

            $parentNode->addChild($node);

            $this->createObjectViewFieldWithNode($group->columns->column, $objectDefinition, $objectView, $node);
        }
    }

    private function createObjectViewFieldWithNode($columns, ObjectDefinition &$objectDefinition, ObjectView &$objectView, ObjectViewNode &$parentNode = null)
    {
        if (!$columns->count()) {
            return;
        }

        $pos = 0;

        foreach ($columns as $column) {
            $pos++;
            $fieldName = (string)$column->field;

            if ($objectDefinition->hasField($fieldName)) {
                if ((string)$column->view) {
                    $view = $column->view;
                } else {
                    $view = ObjectViewField::DEFAULT_VIEW;
                }

                $objectViewField = ObjectViewField::create((string)$column->name, $field = $objectDefinition->getField($fieldName), $view, $pos);

                switch ($field->getType()) {
                    case FieldDefinition::RELATION_TYPE:
                        $options = $column->options;
                        $objectViewField
                            ->setOption('form_type', $this->formtype($options->form_type))
                            ->setOption('tableview', (string)$options->tableview)
                            ->setOption('property', $this->textField($objectDefinition, $field, (string)$options->property))
                        ;
                        break;
                }

                $objectView->addField($objectViewField);

                if ($parentNode) {
                    $node = ObjectViewNode::create((string)$column->name, $type = ObjectViewNode::TYPE_FIELD, $pos, $objectViewField);
                    $node
                        ->setParent($parentNode)
                    ;

                    $node = $this->setTemplate($node, $column->options);

                    $parentNode->addChild($node);
                }
            }
        }
    }

    /******************************************************************************************
     *
     * CREATE TABLEVIEW
     *
     ******************************************************************************************/
    private function generateTableViewActionV1($folders)
    {
        return $this->generateTableViewAction($folders);
    }

    private function generateTableViewAction($folders)
    {
        $nb = 0;

        if (!empty($folders)) {
            $manager = $this->getContainer()->get('pum');

            $finder = new Finder();
            $finder->in($folders);
            $finder->files()->name('*.tableview.xml');

            foreach ($finder as $file) {
                $xml = new \SimpleXMLElement($file->getContents());

                if ($beamName = (string)$xml->beam) {
                    if ($manager->hasBeam($beamName)) {
                        $beam = $manager->getBeam($beamName);

                        if (null !== $xml->objects->object) {
                            foreach ($xml->objects->object as $object) {
                                $objectName = (string)$object->name;

                                if ($beam->hasObject($objectName)) {
                                    $objectDefinition = $beam->getObject($objectName);

                                    if (null !== $object->tableviews->tableview) {
                                        $existed = false;

                                        foreach ($object->tableviews->tableview as $view) {
                                            if (true === $this->deleteExistedTableView($objectDefinition, $view)) {
                                                $existed = true;
                                            }
                                        }

                                        if ($existed) {
                                            $manager->saveBeam($beam);
                                        }

                                        foreach ($object->tableviews->tableview as $view) {
                                            $nb++;
                                            $this->createTableView($objectDefinition, $view);
                                        }
                                    }
                                }
                            }
                        }
                        $manager->saveBeam($beam);
                    }
                }
            }
        }

        return $nb;
    }

    private function deleteExistedTableView(ObjectDefinition &$objectDefinition, $view)
    {
        $viewName = (string)$view->name;

        if ($objectDefinition->hasTableView($viewName)) {
            $objectDefinition->removeTableView($objectDefinition->getTableView($viewName));

            return true;
        }

        return false;
    }

    private function createTableView(ObjectDefinition &$objectDefinition, $view)
    {
        $viewName  = (string)$view->name;
        $tableView = $objectDefinition->createTableView($viewName);

        $tableView->setPrivate($this->bool($view->private));

        if ($this->bool($view->default)) {
            $tableView->setDefault(true);
        }

        if ($objectviewName = $view->objectview) {
            if ($objectDefinition->hasObjectView($objectviewName)) {
                $tableView->setPreferredObjectView($objectDefinition->getObjectView($objectviewName));
            }
        }

        if ($formviewName = $view->formview) {
            if ($objectDefinition->hasFormView($formviewName)) {
                $tableView->setPreferredFormView($objectDefinition->getFormView($formviewName));
            }
        }

        if ($formviewName = $view->formview_create) {
            if ($objectDefinition->hasFormView($formviewName)) {
                $tableView->setPreferredFormCreateView($objectDefinition->getFormView($formviewName));
            }
        }

        if ($template = $view->template) {
            $tableView->setTemplate($template);
        }

        if (null !== $view->columns->column) {
            $sequence = 1;

            foreach ($view->columns->column as $column) {
                $fieldName = (string)$column->field;

                if ($objectDefinition->hasField($fieldName)) {
                    if ((string)$column->view) {
                        $_view = $column->view;
                    } else {
                        $_view = TableViewField::DEFAULT_VIEW;
                    }

                    $tableViewField = TableViewField::create((string)$column->name, $objectDefinition->getField($fieldName), $_view, $sequence++);
                    $tableView->addColumn($tableViewField);

                    if ($this->bool($column->order)) {
                        $tableViewSort = TableViewSort::create($tableViewField, $this->orderType($column->ordertype));
                        $tableView->setDefaultSort($tableViewSort);
                    }
                }
            }
        }

        if (isset($view->filters->filter) && is_array($view->filters->filter)) {
            foreach ($view->filters->filter as $filter) {
                $tableViewFieldName = (string)$filter->name;
                $type = (string)$filter->type;
                $value = (string)$filter->value;

                $tableViewField = $tableView->getColumn($tableViewFieldName);

                if ($tableViewField) {
                    $tableViewField->addFilter(TableViewFilter::create($tableViewField, $type, $value));
                }
            }
        }

        if (isset($view->groups->group) && is_array($view->groups->group)) {
            $em = $this->getContainer()->get('doctrine.orm.entity_manager');
            $groupRepository = $em->getRepository('Pum\Bundle\AppBundle\Entity\Group');

            foreach ($view->groups->group as $g) {
                $group = $groupRepository->findOneByName((string) $g->name);
                if ($group) {
                    $beam = $objectDefinition->getBeam();
                    if (isset($g->projects)) {
                        foreach ($g->projects as $p) {
                            $project = $em->getRepository('Pum\Core\Definition\Project')->findOneByName((string) $p->project);
                            if ($project) {
                                $customView = new CustomView($project, $beam, $objectDefinition, $tableView, $group);
                                $customView->setDefault($this->bool((string) $g->default));
                                $em->persist($customView);
                            }
                        }
                    } else {
                        $projects = $beam->getProjects();
                        foreach ($projects as $project) {
                            $customView = new CustomView($project, $beam, $objectDefinition, $tableView, $group);
                            $customView->setDefault($this->bool((string) $g->default));
                            $em->persist($customView);
                        }
                    }
                }
            }

            $em->flush();
        }
    }

    /******************************************************************************************
     *
     * OTHERS STUFFS
     *
     ******************************************************************************************/
    private function bool($str)
    {
        switch (strtolower($str)) {
            case 'true':
                return true;

            case 'false':
                return false;

            default:
                return (bool)$str;
        }
    }

    private function orderType($str)
    {
        switch (strtolower($str)) {
            case 'desc':
                return 'desc';

            default:
                return 'asc';
        }
    }

    private function formtype($str)
    {
        switch (strtolower((string)$str)) {
            case 'tab':
                return 'tab';

            case 'static':
                return 'static';

            default:
                return 'search';
        }
    }

    private function textField(ObjectDefinition $object, $field, $fieldName)
    {
        $beamName   = $field->getTypeOption('target_beam');
        $beamSeed   = $field->getTypeOption('target_beam_seed');
        $objectName = $field->getTypeOption('target');
        $choices    = array();

        $hash = md5($beamName.$beamSeed.$objectName);

        if (isset($this->cache[$hash])) {
            $choices = $this->cache[$hash];

        } elseif (null !== $beamName && null !== $objectName && null !== $beamSeed) {
            foreach ($this->getContainer()->get('pum')->getAllBeams() as $beam) {
                if ($beam->getName() == $beamName && $beam->getSeed() == $beamSeed) {
                    if ($beam->hasObject($objectName)) {
                        $object = $beam->getObject($objectName);
                        $choices['id'] = 'id';

                        foreach ($object->getFields() as $field) {
                            if ($field->getType() == 'text') {
                                $choices[$field->getName()]= $field->getCamelCaseName();
                            }
                        }

                        $this->cache[$hash] = $choices;
                    }

                    break;
                }
            }
        }

        if (isset($choices[$fieldName])) {
            return $choices[$fieldName];
        }

        return 'id';
    }

    private function setTemplate($node, $options)
    {
        if ((string)$options->template) {
            $node->setOption('template', (string)$options->template);
        }

        return $node;
    }
}
