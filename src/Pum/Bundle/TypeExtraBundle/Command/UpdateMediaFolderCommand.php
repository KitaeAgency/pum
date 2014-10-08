<?php
//php app/console pum:media:updatefolder 01/01/2013 0

namespace Pum\Bundle\TypeExtraBundle\Command;

use Pum\Bundle\CoreBundle\Console\OutputLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateMediaFolderCommand extends ContainerAwareCommand
{
    protected $beamNames = array();

    protected function configure()
    {
        $this
            ->setName('pum:media:updatefolder')
            ->setDescription('Update media to date folder')
            ->addArgument('random', InputArgument::OPTIONAL, 'Date start for medias folder ex: (01/01/2013)', false)
            ->addArgument('delete', InputArgument::OPTIONAL, 'Delete old medias', true)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('profiler')->disable();
        $context = $this->getContainer()->get('pum.context');

        $output->writeln('<info>Start Check</info>');

        $random    = $input->getArgument('random');
        $to_delete = (bool)$input->getArgument('delete');

        if ($random) {
            $random = explode('/', $random);
        }

        if(false === $this->getContainer()->getParameter('pum_type_extra.media.storage.filesystem.datefolder')) {
            $output->writeln('<info>Nothing to update. You have to active date_folder in pum_type_extra to handle date folder for medias</info>');
            $output->writeln('<info>End Check</info>');
            exit;
        }

        $directory = $this->getContainer()->getParameter('pum_type_extra.media.storage.filesystem.directory');
        $webpath   = $this->getContainer()->getParameter('pum_type_extra.media.storage.filesystem.path');
        $dirname   = dirname($directory.$webpath.'toto.php').'/';

        $output->writeln('<info>'.$dirname.'/</info>');

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

        $db   = $this->getContainer()->getParameter('database_name');
        $host = $this->getContainer()->getParameter('database_host');
        $user = $this->getContainer()->getParameter('database_user');
        $pwd  = $this->getContainer()->getParameter('database_password');

        $pdo   = new \PDO('mysql:host='.$host.';dbname='.$db, $user, $pwd);
        $count = 0;

        $style = new \Symfony\Component\Console\Formatter\OutputFormatterStyle('green', 'black', array('bold'));
        $output->getFormatter()->setStyle('updated', $style);
        $style = new \Symfony\Component\Console\Formatter\OutputFormatterStyle('blue', 'black', array('bold'));
        $output->getFormatter()->setStyle('inserted', $style);
        $style = new \Symfony\Component\Console\Formatter\OutputFormatterStyle('magenta', 'black', array('bold'));
        $output->getFormatter()->setStyle('deleted', $style);
        $style = new \Symfony\Component\Console\Formatter\OutputFormatterStyle('red', 'black', array('bold'));
        $output->getFormatter()->setStyle('error', $style);

        foreach ($tables as $project => $objects) {
            foreach ($objects as $object => $fields) {
                $query = '';
                $query .= 'SELECT id, '.implode(', ', $fields).' FROM obj__'.$project.'__'.$object.' ORDER BY id asc';

                foreach ($pdo->query($query) as $name => $value) {
                    foreach ($fields as $field) {
                        if ($value[$field]) {
                            if (is_file($dirname.$value[$field]) && strpos($value[$field], '/') === false) {
                                if (is_array($random) && count($random) == 3) {
                                    $rand        = mt_rand(0, 365);
                                    $random_date = date("Y/m/d", mktime(0, 0, 0, $random[1]  , $random[0] + $rand, $random[2]));
                                    $new_dir     = $random_date.'/'.substr($value[$field], 0, 1);
                                } else {
                                    $new_dir     = date ("Y/m/d", filemtime($dirname.$value[$field])).'/'.substr($value[$field], 0, 1);
                                }

                                $new_file = $new_dir.$value[$field];
                                if (!is_dir($dirname.$new_dir)) {
                                    @mkdir($dirname.$new_dir, 0777, true);
                                }

                                if (is_dir($dirname.$new_dir)) {
                                    if (false !== @copy($dirname.$value[$field], $dirname.$new_file)) {
                                        $count++;
                                        $query_update = 'UPDATE obj__'.$project.'__'.$object.' SET '.$field.' = "'.$new_file.'" WHERE id = '.$value['id'];
                                        $pdo->prepare($query_update)->execute();

                                        if ($to_delete) {
                                            @unlink($dirname.$value[$field]);
                                        }
                                    }
                                }

                                $output->writeln('<inserted>FILE : '.$value[$field].' updated from '.$object.' '.$value['id'].'</inserted>');
                            }
                        }
                    }
                }
            }
        }

        $output->writeln('<info>'.$count.' files updated</info>');
        $output->writeln('<info>End Check</info>');
    }
}
