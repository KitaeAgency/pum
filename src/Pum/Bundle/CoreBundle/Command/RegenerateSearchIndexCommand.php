<?php 

namespace Pum\Bundle\CoreBundle\Command;

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
        $container   = $this->getContainer();
        $objectName  = $input->getArgument('objectname');

        foreach ($container->get('pum')->getAllBeams() as $beam) {
            foreach ($beam->getObjects() as $object) {
                if ($object->isSearchEnabled()) {
                    if (null == $objectName || $objectName == $object->getName()) {
                        $object->raiseOnce(Events::OBJECT_DEFINITION_SEARCH_UPDATE, new ObjectDefinitionEvent($object));
                    }
                }
            }

            $container->get('pum')->saveBeam($beam);
        }

        if (null === $objectName) {
            $output->writeln(sprintf('Regenerate search index succeed for all objects'));
        } else {
            $output->writeln(sprintf('Regenerate search index succeed for object "%s"', $objectName));
        }
    }

}
