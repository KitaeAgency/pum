<?php
//php app/console pum:media:cleanup

namespace Pum\Bundle\TypeExtraBundle\Command;

use Pum\Bundle\CoreBundle\Console\OutputLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupMediaCommand extends ContainerAwareCommand
{
    protected $pdo          = null;
    protected $start_offset = 0;
    protected $query        = null;
    protected $output       = null;
    protected $dirname      = null;

    protected function configure()
    {
        $this
            ->setName('pum:media:cleanup')
            ->setDescription('CLeanup media')
            ->addArgument('offset_start', InputArgument::OPTIONAL, 'Start delete index', 0)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $this->output->writeln('<info>Start CleanUp</info>');
        $this->getContainer()->get('profiler')->disable();

        $style = new \Symfony\Component\Console\Formatter\OutputFormatterStyle('green', 'black', array('bold'));
        $this->output->getFormatter()->setStyle('updated', $style);
        $style = new \Symfony\Component\Console\Formatter\OutputFormatterStyle('blue', 'black', array('bold'));
        $this->output->getFormatter()->setStyle('inserted', $style);
        $style = new \Symfony\Component\Console\Formatter\OutputFormatterStyle('magenta', 'black', array('bold'));
        $this->output->getFormatter()->setStyle('deleted', $style);
        $style = new \Symfony\Component\Console\Formatter\OutputFormatterStyle('red', 'black', array('bold'));
        $this->output->getFormatter()->setStyle('error', $style);

        $context            = $this->getContainer()->get('pum.context');
        $this->start_offset = $input->getArgument('offset_start');

        $db                 = $this->getContainer()->getParameter('database_name');
        $host               = $this->getContainer()->getParameter('database_host');
        $user               = $this->getContainer()->getParameter('database_user');
        $pwd                = $this->getContainer()->getParameter('database_password');

        $this->pdo          = new \PDO('mysql:host='.$host.';dbname='.$db, $user, $pwd);

        $directory     = $this->getContainer()->getParameter('pum_type_extra.media.storage.filesystem.directory');
        $webpath       = $this->getContainer()->getParameter('pum_type_extra.media.storage.filesystem.path');
        $this->dirname = dirname($directory.$webpath.'toto.php').'/';

        $count    = 0;
        $tables   = array();
        $projects = $context->getAllProjects();

        foreach ($projects as $project) {
            $objects = $project->getObjects();

            foreach ($objects as $object) {
                $fields = $object->getFields();

                foreach ($fields as $key => $field) {
                    if ($field->getType() == 'media') {
                        $tables[$project->getName()][$object->getName()][] = $key.'_id';
                    }
                }
            }
        }

        $i           = 0;
        $this->query = '';

        foreach ($tables as $project => $objects) {
            foreach ($objects as $object => $fields) {
                if ($i > 0) {
                    $this->query .= ' UNION ';
                }

                $this->query .= '(SELECT id FROM obj__'.$project.'__'.$object;

                foreach ($fields as $j => $field) {
                    $where = ' WHERE ';
                    if ($j > 0) {
                        $where = ' OR ';
                    }

                    $this->query .= $where;
                    $this->query .= $field.' = ":media" ';
                }

                $this->query .= ')';
                $i++;
            }
        }

        $this->readDir($this->dirname, $count);

        $this->output->writeln('<info>End CleanUp</info>');
    }

    private function readDir($dirname, &$count) {
        //$this->output->writeln('<info>CLEAN FOLDER: '.$dirname.'</info>');
        $countfile = 0;
        $dir       = opendir($dirname);

        while($file = readdir($dir)) {
            if($file != '.' && $file != '..') {
                if (!is_dir($dirname.$file)) {
                    $this->cleanUp($dirname.$file, $count);
                } elseif(strpos($file, '_') === false) {
                    $this->readDir($dirname.$file.'/', $count);
                }
            }
        }

        closedir($dir);
    }

    private function cleanUp($file, &$count)
    {
        if ($count < $this->start_offset) {
            return;
        }

        $folder   = dirname($file);
        $filename = basename($file);
        $media_id = str_replace($this->dirname, '', $folder.'/'.$filename);

        $temp_query = str_replace(':media', $media_id, $this->query);
        $delete     = true;
        foreach ($this->pdo->query($temp_query) as $result) {
            $delete = false;
        }

        if ($delete) {
            @unlink($file);
            $count++;
            $this->output->writeln('<deleted>FILE : '.$file.' DELETED</deleted>');
        } else {
            $this->output->writeln('<updated>FILE : '.$file.' IN BASE</updated>');
        }

        if ($count % 500 == 0 && $count > 0) {
            $this->output->writeln('<error>'.$count.' FILES DELETED</error>');
            $this->output->writeln('<info>Memory usage : ' . (memory_get_usage() / 1024) . ' KB</info>');
        }
    }
}
