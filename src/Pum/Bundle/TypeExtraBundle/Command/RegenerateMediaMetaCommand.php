<?php 

namespace Pum\Bundle\TypeExtraBundle\Command;

use Pum\Core\Events;
use Pum\Core\Event\ObjectDefinitionEvent;
use Pum\Bundle\CoreBundle\Console\OutputLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Pum\Core\Extension\Util\Namer;

class RegenerateMediaMetaCommand extends ContainerAwareCommand
{
    const MEDIA_TYPE = 'media';

    protected $beamNames = array();

    protected function configure()
    {
        $this
            ->setName('pum:media:regeneratemeta')
            ->setDescription('Regenerate media meta')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $storage   = $container->get('type_extra.media.storage.driver');
        $dir       = realpath($container->get('kernel')->getRootDir() . '/../web');

        foreach ($container->get('pum')->getAllProjects() as $project) {
            $em = $container->get('pum.context')->setProjectName($project->getName())->getProjectOEM();

            foreach ($project->getBeams() as $beam) {
                foreach ($beam->getObjects() as $object) {
                    foreach ($object->getFields() as $field) {
                        if ($field->getType() == self::MEDIA_TYPE) {

                            $getMethod = 'get'.ucfirst(Namer::toCamelCase($field->getName()));
                            $setMethod = 'set'.ucfirst(Namer::toCamelCase($field->getName()));

                            $objs = $em->getRepository($object->getName())->findAll();

                            foreach ($objs as $obj) {
                                if (null !== $media = $obj->$getMethod()) {
                                    if ($media->exists() && null === $media->getMime()) {
                                        $file = new \SplFileInfo(realpath($dir.$storage->getWebPath($media)));
                                        if ($file->isFile()) {
                                            $media->setMime($storage->guessMime($file));

                                            if ($media->isImage()) {
                                                list($width, $height) = $storage->guessImageSize($file);
                                                $media->setWidth($width);
                                                $media->setHeight($height);
                                            }

                                            $obj->$setMethod($media);
                                            $em->persist($obj);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $em->flush();
        }

        $output->writeln(sprintf('Regenerate media meta succeed'));
    }

}
