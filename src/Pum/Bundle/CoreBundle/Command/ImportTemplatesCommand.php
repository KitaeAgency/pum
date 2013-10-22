<?php 

namespace Pum\Bundle\CoreBundle\Command;

use Pum\Bundle\CoreBundle\Console\OutputLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ImportTemplatesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('pum:templates:import-templates')
            ->setDescription('Import templates template from folder : Resources/pum_views/')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        if ($container->hasParameter('pum.view.mode.dbal')) {
            $nb = $container->get('pum.view_feature.dbal')->importTemplateViewFromFilessystem();

            $output->writeln(sprintf('Import templates : '.$nb));
        } else {
            $output->writeln(sprintf('Import templates : Dbal templates mode is disabled'));
        }
    }
}
