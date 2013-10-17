<?php 

namespace Pum\Bundle\CoreBundle\Command;

use Pum\Bundle\CoreBundle\Console\OutputLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ImportFieldsTemplatesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('pum:fields:import-templates')
            ->setDescription('Import fields template from folder : Resources/pum_views/field')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $container->get('pum.view_feature.field')->updateViewTemplates();

        $output->writeln(sprintf('Import field templates : Ok'));
    }

}
