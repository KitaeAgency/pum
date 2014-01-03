<?php 

namespace Pum\Bundle\CoreBundle\Command;

use Pum\Core\Definition\Beam;
use Pum\Bundle\CoreBundle\Console\OutputLogger;
use Pum\Core\Extension\Util\Namer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ImportBeamCommand extends ContainerAwareCommand
{
    protected $beamNames = array();

    protected function configure()
    {
        $this
            ->setName('pum:beam:import')
            ->setDescription('Import beams from folder : Resources/pum')
            ->addOption('detail', null, InputOption::VALUE_OPTIONAL, 'Show beam import progression', true)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $dirs = array();
        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_dir($dir = dirname($reflection->getFilename()).'/Resources/pum')) {
                $dirs[] = $dir;
            }
        }

        $finder = new Finder();
        $finder->files()->name('*.json');

        if (!empty($dirs)) {
            foreach ($dirs as $dir) {
                $finder->in($dir);
            }

            foreach ($finder as $file) {
                if (!$arrayedBeam = json_decode($file->getContents(), true)) {
                    $output->writeln(sprintf('File <error>%s</error> is invalid json', $file->getFilename()));
                } else {
                    try {
                        $beam =
                            Beam::createFromArray($arrayedBeam)
                                ->setName($this->guessBeamName($file->getFilename()))
                        ;
                        $container->get('pum')->saveBeam($beam);
                        if ($input->getOption('detail')) {
                            $output->writeln(sprintf('Import success for beam : <info>%s</info>', $beam->getName()));
                        }
                    } catch (\InvalidArgumentException $e) {
                        $output->writeln(sprintf('Json %s content is invalid : %s', $file->getFilename(), $e->getMessage()));
                    }
                }
            }
        }
    }

    protected function guessBeamName($filename)
    {
        $filename = Namer::toLowercase(str_replace('.json', '', strtolower($filename)));

        $i = 0;
        $beanName = $filename;
        while (in_array($beanName, $this->beamNames)) {
            $beanName = $filename . (string)$i;
            $i++;
        }

        $this->beamNames[$beanName] = $beanName;

        return $beanName;
    }
}
