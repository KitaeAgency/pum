<?php 

namespace Pum\Bundle\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Common\Collections\Criteria;

class PumNotificationDispatcherCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('pum:notification:dispatch')
            ->setDescription('Dispatch the delayed notification emails')
            ->addOption('detail', null, InputOption::VALUE_OPTIONAL, 'Show notification dispatch progression', true)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $notificationRepository = $container->get('pum.notification_repository');

        $expr = Criteria::expr();
        $criteria = Criteria::create();
        $criteria->where(
            $expr->andX(
                $expr->eq('email', true),
                $expr->lte('delayed', new \DateTime()),
                $expr->eq('sent', null)
            )
        );

        $notifications = $notificationRepository->matching($criteria);

        foreach ($notifications as $notification) {
            $container->get('pum.notification')->send($notification);
        }
    }
}
