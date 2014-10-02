<?php

namespace Pum\Bundle\CoreBundle\Command;

use Pum\Bundle\CoreBundle\Console\OutputLogger;
use Pum\Core\Definition\Project;
use Pum\Core\Extension\EmFactory\EmFactoryExtension;
use Pum\Core\Event\ProjectEvent;
use Pum\Core\Events;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

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
        $this->executeCommand('doctrine:schema:update', array('--force' => true), $output);

        foreach ($this->getContainer()->get('pum')->getAllProjects() as $project) {
            $this->doUpdate($project, $output);
        }
    }

    public function doUpdate(Project $project, OutputInterface $output)
    {
        $project->raise(Events::PROJECT_SCHEMA_UPDATE, new ProjectEvent($project));
        $this->getContainer()->get('pum')->saveProject($project);
        $output->writeln('Updated project <info>'.$project->getName().'</info>');
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


