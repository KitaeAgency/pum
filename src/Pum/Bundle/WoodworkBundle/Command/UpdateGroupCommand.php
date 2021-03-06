<?php

namespace Pum\Bundle\WoodworkBundle\Command;

use Pum\Core\Definition\Beam;
use Pum\Bundle\CoreBundle\Console\OutputLogger;
use Pum\Core\Extension\Util\Namer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class UpdateGroupCommand extends ContainerAwareCommand
{
    protected $groups = array();

    protected function configure()
    {
        $this
            ->setName('pum:group:update')
            ->setDescription('Update groups')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $connection = $em->getConnection();
        $tables = array();

        foreach ($connection->query('SHOW TABLES')->fetchAll() as $table) {
            $tables[] = reset($table);
        }

        if (in_array('ww_user_group', $tables)) {
            try {
                $users = $em->getRepository('Pum\Bundle\AppBundle\Entity\User')->findBy(array('group' => null));
                foreach ($users as $user) {
                    $groups = $connection->fetchAssoc('SELECT group_id as `group` FROM ww_user_group WHERE user_id = :user', array('user' => $user->getId()));

                    if (!empty($groups)) {
                        $group = $em->getPartialReference('Pum\Bundle\AppBundle\Entity\Group', reset($groups));

                        if ($group) {
                            $user->setGroup($group);

                            $em->persist($user);
                        }
                    }
                }
                $em->flush();
            } catch (\Exception $e) {
                $output->write(sprintf('Update failed with message: "%s"', $e->getMessage()));
            }
        } else {
            $output->write('Table ww_user_group doesn\'t exists anymore, group won\'t be updated');
        }

        $groups = $em->getRepository('Pum\Bundle\AppBundle\Entity\Group')->findAll();
        foreach ($groups as $group) {
            if (!$group->getAlias()) {
                $group->setAlias($group->getName());
                $group->setName('group_' . preg_replace('/[^a-z0-9]/', '_', strtolower($group->getAlias())));

                if ($group->getName() === 'group_administrators') {
                    $group->setAdmin(true);
                }

                $em->persist($group);
            }
        }
        $em->flush();
    }
}
