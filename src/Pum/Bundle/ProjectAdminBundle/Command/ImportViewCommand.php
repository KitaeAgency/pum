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
use Pum\Core\Definition\View\TableViewSort;
use Pum\Core\Definition\View\TableViewField;
use Pum\Core\Definition\View\ObjectViewField;
use Pum\Core\Definition\View\FormView;
use Pum\Core\Definition\View\FormViewField;

class ImportViewCommand extends ContainerAwareCommand
{
    protected $cache = array();

    protected function configure()
    {
        $this
            ->setName('pum:view:import')
            ->setDescription('Import views from folder : Resources/pum/view')
            ->addArgument('version', null, InputArgument::OPTIONAL, 'v1 or v2', 'v2')
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

            // TreeView
            $this->updateViewStructure($output);
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

        if ($this->bool($view->default)) {
            $objectDefinition->setDefaultFormView(null);
        }

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
            $objectDefinition->setDefaultFormView($formView);
        }

        if (null !== $view->columns->column) {
            $sequence = 1;

            foreach ($view->columns->column as $column) {
                $fieldName = (string)$column->field;

                if ($objectDefinition->hasField($fieldName)) {
                    $formViewField = FormViewField::create((string)$column->name, $field = $objectDefinition->getField($fieldName), FormViewField::DEFAULT_VIEW, $sequence++, (string)$column->placeholder, (string)$column->help, $this->bool($column->disabled));

                    switch ($field->getType()) {
                        case FieldDefinition::RELATION_TYPE:
                            $options = $column->options;
                            $formViewField
                                ->setOption('form_type', $this->formtype($options->form_type))
                                ->setOption('property', $this->textField($objectDefinition, $field, (string)$options->property))
                                ->setOption('allow_add', $this->bool($options->allow_add))
                                ->setOption('allow_select', $this->bool($options->allow_select))
                                ->setOption('allow_delete', $this->bool($options->allow_delete))
                            ;
                            break;
                        
                        case 'html':
                            $options = $column->options;
                            $formViewField->setOption('config_json', (string)$options->config_json);
                            break;
                    }

                    $formView->addField($formViewField);
                }
            }
        }
    }

    /******************************************************************************************
     * 
     * CREATE OBJECTVIEW
     *
     ******************************************************************************************/
    private function generateObjectViewAction($folders)
    {
        return $this->generateObjectViewActionV1($folders);
    }

    private function generateObjectViewActionV1($folders)
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
                                            $this->createObjectView($objectDefinition, $view);
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

        if ($this->bool($view->default)) {
            $objectDefinition->setDefaultObjectView(null);
        }

        if ($objectDefinition->hasObjectView($viewName)) {
            $objectDefinition->removeObjectView($objectDefinition->getObjectView($viewName));

            return true;
        }

        return false;
    }

    private function createObjectView(ObjectDefinition &$objectDefinition, $view)
    {
        $viewName   = (string)$view->name;
        $objectView = $objectDefinition->createObjectView($viewName);

        $objectView->setPrivate($this->bool($view->private));

        if ($this->bool($view->default)) {
            $objectDefinition->setDefaultObjectView($objectView);
        }

        if (null !== $view->columns->column) {
            $sequence = 1;

            foreach ($view->columns->column as $column) {
                $fieldName = (string)$column->field;

                if ($objectDefinition->hasField($fieldName)) {
                    $objectViewField = ObjectViewField::create((string)$column->name, $objectDefinition->getField($fieldName), ObjectViewField::DEFAULT_VIEW, $sequence++);
                    $objectView->addField($objectViewField);
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

        if ($this->bool($view->default)) {
            $objectDefinition->setDefaultTableView(null);
        }

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
            $objectDefinition->setDefaultTableView($tableView);
        }

        if ($objectviewName = $view->objectview) {
            if ($objectDefinition->hasObjectView($objectviewName)) {
                $tableView->setPreferredObjectView($objectDefinition->getObjectView($objectviewName));
            }
        }

        if ($formviewName = $view->objectview) {
            if ($objectDefinition->hasFormView($formviewName)) {
                $tableView->setPreferredFormView($objectDefinition->getFormView($formviewName));
            }
        }

        if (null !== $view->columns->column) {
            $sequence = 1;

            foreach ($view->columns->column as $column) {
                $fieldName = (string)$column->field;

                if ($objectDefinition->hasField($fieldName)) {
                    $tableViewField = TableViewField::create((string)$column->name, $objectDefinition->getField($fieldName), TableViewField::DEFAULT_VIEW, $sequence++);
                    $tableView->addColumn($tableViewField);

                    if ($this->bool($column->order)) {
                        $tableViewSort = TableViewSort::create($tableViewField, $this->orderType($column->ordertype));
                        $tableView->setDefaultSort($tableViewSort);
                    }
                }
            }
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

    private function updateViewStructure(OutputInterface $output)
    {
        if (property_exists(new FormView, 'view')) {
            $this->executeCommand('pum:view:update', array(), $output);
        }
    }

    private function executeCommand($command, $arguments, OutputInterface $output)
    {
        $command              = $this->getApplication()->find($command);
        $arguments['command'] = $command;
        $input                = new ArrayInput($arguments);

        $returnCode = $command->run($input, $output);

        if($returnCode == 0) {
            return true;
        }

        return false;
    }
}
