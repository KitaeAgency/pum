<?php 

namespace Pum\Bundle\ProjectAdminBundle\Command;

use Pum\Core\Definition\View\FormViewNode;
use Pum\Bundle\CoreBundle\Console\OutputLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateFormViewCommand extends ContainerAwareCommand
{
    protected $beamNames = array();

    protected function configure()
    {
        $this
            ->setName('pum:view:update')
            ->setDescription('Update formview structure')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timestart  = microtime(true);

        foreach ($this->getContainer()->get('pum')->getAllBeams() as $beam) {
            foreach ($beam->getObjects() as $object) {
                foreach ($object->getFormViews() as $formView) {
                    if (null === $formView->getView()) {
                        $formView->setView(FormViewNode::create($name = 'ROOT', $type = FormViewNode::TYPE_ROOT, $sequence = 0));
                        $output->writeln(sprintf('Updating formview "%s"', $formView->getName()));
                    }
                }
            }

            $this->getContainer()->get('pum')->saveBeam($beam);
        }

        $output->writeln(sprintf('Update formview structure done'));
        $output->writeln(sprintf("Script duration " . number_format(microtime(true)-$timestart, 3) . " seconds"));
    }

}
