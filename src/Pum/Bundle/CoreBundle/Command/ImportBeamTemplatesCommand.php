<?php 

namespace Pum\Bundle\CoreBundle\Command;

use Pum\Bundle\CoreBundle\Console\OutputLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ImportBeamTemplatesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('pum:beams:import-templates')
            ->setDescription('Import beams template from folder : Resources/pum_views/beam/{beam}/{view}')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        if ($container->hasParameter('pum.view.mode.dbal')) {
            $nb = $container->get('pum.view_feature.dbal')->importBeamViewFromFilessystem();

            $output->writeln(sprintf('Import beam templates : '.$nb));
        } else {
            $output->writeln(sprintf('Import beam templates : Dbal templates mode is disabled'));
        }
    }
}
