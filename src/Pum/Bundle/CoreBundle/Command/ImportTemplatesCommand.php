<?php 

namespace Pum\Bundle\CoreBundle\Command;

use Pum\Bundle\CoreBundle\Console\OutputLogger;
use Pum\Core\Extension\View\Template\Template;
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
            ->setName('pum:templates:import')
            ->setDescription('Import templates template from folder : Resources/pum_views/')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $view      = $container->get('pum.view_storage.dbal');

        if ($container->hasParameter('pum.view.mode.dbal')) {
            $folders = $this->getPumTemplatesFolders();
            $nb      = 0;

            if (!empty($folders)) {
                $finder = new Finder();
                $finder->in($folders);
                $finder->files()->name('*.twig');

                foreach ($finder as $file) {
                    $realPath = $file->getRealPath();
                    if (false !== $pumPath = $this->guessPumPath($realPath)) {
                        $nb++;
                        $view->storeTemplate(Template::create($pumPath, $file->getContents(), $file->getMTime()), $erase = true);
                    }
                }
            }

            $output->writeln(sprintf('Import templates : '.$nb));
        } else {
            $output->writeln(sprintf('Import templates : Dbal templates mode is disabled'));
        }
    }

    protected function getPumTemplatesFolders()
    {
        $container = $this->getContainer();

        $folders = array();
        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
            if (is_dir($dir = $container->getParameter('kernel.root_dir').'/Resources/'.$bundle.'/pum_views')) {
                $folders[] = $dir;
            }

            $reflection = new \ReflectionClass($class);
            if (is_dir($dir = dirname($reflection->getFilename()).'/Resources/pum_views')) {
                $folders[] = $dir;
            }
        }

        return $folders;
    }

    protected function guessPumPath($realPath)
    {
        $pumPath  = explode(DIRECTORY_SEPARATOR.'pum_views'.DIRECTORY_SEPARATOR, $realPath);

        if (count($pumPath) > 1) {
            unset($pumPath[0]);
            $pumPath = str_replace(DIRECTORY_SEPARATOR, '/', implode('', $pumPath));
            
            if ($pumPath) {
                return $pumPath;
            }
        }

        return false;
    }
}
