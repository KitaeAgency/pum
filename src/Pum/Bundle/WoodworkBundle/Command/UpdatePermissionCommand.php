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

class UpdatePermissionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('pum:permission:update')
            ->setDescription('Update permissions schema')
        ;
    }

    protected function updatePermission($type = 'group')
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $repository = $em->getRepository('Pum\Bundle\AppBundle\Entity\\'. ucfirst($type) .'Permission');
        if (!$repository) {
            throw new \Exception('Invalid repository');
        }

        $permissions = $repository
            ->createQueryBuilder('p')
            ->groupBy('p.' . $type)
            ->addGroupBy('p.project')
            ->addGroupBy('p.beam')
            ->addGroupBy('p.object')
            ->addGroupBy('p.instance')
            ->getQuery()
            ->getResult();

        foreach ($permissions as $permission) {
            if (!is_numeric($permission->getAttribute())) {
                $allPermissions = $repository
                    ->createQueryBuilder('p1')
                    ->where('p1.' . $type . '= :type')
                    ->andWhere('p1.project = :project')
                    ->setParameters(array(
                        'type' => $type == 'group' ? $permission->getGroup() : $permission->getUser(),
                        'project' => $permission->getProject()
                    ));

                if ($permission->getBeam() === null) {
                    $allPermissions->andWhere('p1.beam IS NULL');
                } else {
                    $allPermissions
                        ->andWhere('p1.beam = :beam')
                        ->setParameter('beam', $permission->getBeam());
                }

                if ($permission->getObject() === null) {
                    $allPermissions->andWhere('p1.object IS NULL');
                } else {
                    $allPermissions
                        ->andWhere('p1.object = :object')
                        ->setParameter('object', $permission->getObject());
                }

                if ($permission->getInstance() === null) {
                    $allPermissions->andWhere('p1.instance IS NULL');
                } else {
                    $allPermissions
                        ->andWhere('p1.instance = :instance')
                        ->setParameter('instance', $permission->getInstance());
                }

                $attributes = array();
                $allPermissions = $allPermissions->getQuery()->getResult();

                foreach ($allPermissions as $allPermission) {
                    $attributes[] = $allPermission->getAttribute();

                    if ($allPermission->getId() != $permission->getId()) {
                        $em->remove($allPermission);
                    }
                }

                $permission->setAttributes($attributes);
                $em->persist($permission);

                $em->flush();
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Updating group permissions... ');
        $this->updatePermission('group');
        $output->writeln('Done');

        $output->write('Updating user permissions... ');
        $this->updatePermission('user');
        $output->writeln('Done');
    }
}
