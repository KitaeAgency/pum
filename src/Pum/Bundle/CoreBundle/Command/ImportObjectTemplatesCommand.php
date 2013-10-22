<?php 

namespace Pum\Bundle\CoreBundle\Command;

use Pum\Bundle\CoreBundle\Console\OutputLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ImportObjectTemplatesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('pum:objects:import-templates')
            ->setDescription('Import objects template from folder : Resources/pum_views/object/{beam}/{object}/{view}')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        if ($container->hasParameter('pum.view.mode.dbal')) {
            $nb = $container->get('pum.view_feature.dbal')->importObjectViewFromFilessystem();

            $output->writeln(sprintf('Import objects templates : '.$nb));
        } else {
            $output->writeln(sprintf('Import objects templates : Dbal templates mode is disabled'));
        }
    }
}
