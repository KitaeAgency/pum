<?php 

namespace Pum\Bundle\ProjectAdminBundle\Command;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Definition\View\FormView;
use Pum\Core\Definition\View\FormViewNode;
use Pum\Core\Definition\View\ObjectView;
use Pum\Core\Definition\View\ObjectViewNode;
use Pum\Bundle\CoreBundle\Console\OutputLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateViewCommand extends ContainerAwareCommand
{
    protected $beamNames = array();

    protected function configure()
    {
        $this
            ->setName('pum:view:update')
            ->setDescription('Update views structure')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timestart = microtime(true);

        if (!property_exists(new FormView, 'view')) {
            $output->writeln(sprintf('No need to update formview structure'));

            return;
        }

        foreach ($this->getContainer()->get('pum')->getAllBeams() as $beam) {
            $save = false;

            foreach ($beam->getObjects() as $object) {
                // Formview update
                foreach ($object->getFormViews() as $formView) {
                    if (null === $formView->getView()) {
                        $save     = true;
                        $rootNode = FormViewNode::create($name = 'ROOT', $type = FormViewNode::TYPE_ROOT, $position = 0);
                        $formView->setView($rootNode);

                        $fieldsType = array();
                        foreach ($formView->getFields() as $formViewField) {
                            if ($formViewField->getField()->getType() == FieldDefinition::RELATION_TYPE) {
                                if ($formViewField->hasOption('form_type') && $formViewField->getOption('form_type') == 'tab') {
                                    $fieldsType['relation'.$formViewField->getId()][] = $formViewField;
                                    continue;
                                }
                            }

                            $fieldsType['regular'][] = $formViewField;
                        }

                        if (count($fieldsType) == 1) {
                            $sequence = 1;
                            foreach (reset($fieldsType) as $formViewField) {
                                $node = FormViewNode::create(ucfirst($formViewField->getLabel()), $type = FormViewNode::TYPE_FIELD, $sequence++, $formViewField);
                                $node
                                    ->setParent($rootNode)
                                ;

                                $rootNode->addChild($node);
                            }

                        } else {
                            $sequence = 1;
                            foreach ($fieldsType as $type => $tab) {
                                if (count($tab) == 1) {
                                    $label = ucfirst($tab[0]->getLabel());
                                } else {
                                    $label = $this->getContainer()->get('translator')->trans('pa.object.regular.fields', array(), 'pum');
                                }

                                $tabNode  = FormViewNode::create($label, $type = FormViewNode::TYPE_TAB, $sequence++);

                                $tabNode->setParent($rootNode);
                                $rootNode->addChild($tabNode);

                                $pos = 1;
                                foreach ($tab as $formViewField) {
                                    $node = FormViewNode::create(ucfirst($formViewField->getLabel()), $type = FormViewNode::TYPE_FIELD, $pos++, $formViewField);
                                    $node
                                        ->setParent($tabNode)
                                    ;

                                    $tabNode->addChild($node);
                                }
                            }
                        }

                        $output->writeln(sprintf('Updating formview "%s"', $formView->getName()));
                    }
                }

                // Objectview update
                foreach ($object->getObjectViews() as $objectView) {
                    if (null === $objectView->getView()) {
                        $save     = true;
                        $rootNode = ObjectViewNode::create($name = 'ROOT', $type = ObjectViewNode::TYPE_ROOT, $position = 0);
                        $objectView->setView($rootNode);

                        $sequence = 1;
                        foreach ($objectView->getFields() as $objectViewField) {
                            $node = ObjectViewNode::create(ucfirst($objectViewField->getLabel()), $type = ObjectViewNode::TYPE_FIELD, $sequence++, $objectViewField);
                            $node
                                ->setParent($rootNode)
                            ;

                            $rootNode->addChild($node);
                        }

                        $output->writeln(sprintf('Updating objectview "%s"', $objectView->getName()));
                    }
                }
            }

            if ($save) {
                $this->getContainer()->get('pum')->saveBeam($beam);
            }
        }

        $output->writeln(sprintf('Update formview structure done'));
        $output->writeln(sprintf("Script duration " . number_format(microtime(true)-$timestart, 3) . " seconds"));
    }

}
