<?php

namespace Pum\Bundle\CoreBundle\Command;

use Pum\Bundle\CoreBundle\Console\OutputLogger;
use Pum\Core\Definition\Project;
use Pum\Core\Extension\EmFactory\EmFactoryExtension;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateSchemaCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('pum:schema:update')
            ->setDescription('Updates dynamic schema')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getContainer()->get('pum')->getAllProjects() as $project) {
            $output->writeln(sprintf('Updating <info>%s</info>', $project->getName()));
            $this->doUpdate($project, $output);
        }
    }

    public function doUpdate(Project $project, OutputInterface $output)
    {
        $this->getContainer()->get('pum')->saveProject($project);
        $output->writeln('Updated project <info>'.$project->getName().'</info>');
    }
}
