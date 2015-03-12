<?php 

namespace Pum\Bundle\ProjectAdminBundle\Command;

use Pum\Core\Events;
use Pum\Core\Event\ObjectDefinitionEvent;
use Pum\Bundle\CoreBundle\Console\OutputLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RegenerateSearchIndexCommand extends ContainerAwareCommand
{
    protected $beamNames = array();

    protected function configure()
    {
        $this
            ->setName('pum:search:regenerateindex')
            ->setDescription('Regenerate search index')
            ->addArgument(
                'objectname',
                InputArgument::OPTIONAL,
                'Nom de l\'object à update ?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        gc_enable();

        $timestart  = microtime(true);
        $objectName = $input->getArgument('objectname');

        $this->getContainer()->get('profiler')->disable();

        foreach ($this->getContainer()->get('pum')->getAllBeams() as $beam) {
            foreach ($beam->getObjectsBy(array('searchEnabled' => true)) as $object) {
                if (null == $objectName || $objectName == $object->getName()) {
                    $object->raiseOnce(Events::OBJECT_DEFINITION_SEARCH_UPDATE, new ObjectDefinitionEvent($object));
                }
            }

            $this->getContainer()->get('pum')->saveBeam($beam);
        }

        if (null === $objectName) {
            $output->writeln(sprintf('Regenerate search index succeed for all objects'));
        } else {
            $output->writeln(sprintf('Regenerate search index succeed for object "%s"', $objectName));
        }

        $output->writeln(sprintf("Script duration " . number_format(microtime(true)-$timestart, 3) . " seconds"));
        $output->writeln(sprintf('Memory usage : ' . number_format((memory_get_usage() / 1024/1024),3) . ' MO'));
    }

}
