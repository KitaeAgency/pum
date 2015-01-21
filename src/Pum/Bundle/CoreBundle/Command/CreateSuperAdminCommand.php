<?php

namespace Pum\Bundle\CoreBundle\Command;

use Pum\Bundle\CoreBundle\Console\OutputLogger;
use Pum\Core\Definition\Project;
use Pum\Core\Extension\EmFactory\EmFactoryExtension;
use Pum\Core\Event\ProjectEvent;
use Pum\Core\Events;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateSuperAdminCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('pum:users:create_superadmin')
            ->setDescription('Create super admin group and an admin usez to it')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'email of the admin')
            ->addOption('fullname', null, InputOption::VALUE_OPTIONAL, 'fullname of the admin')
            ->addOption('pwd', null, InputOption::VALUE_OPTIONAL, 'password of the admin')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $email = $input->getOption('email');
        if (!$fullname = $input->getOption('fullname')) {
            $fullname = 'Super Admin';
        }
        if (!$pwd = $input->getOption('pwd')) {
            $pwd = $this->createPwd();
        }

        $securityManager = $container->get('pum.security.manager');
        $user            = $securityManager->createSuperAdmin($email, $fullname, $pwd);

        $output->writeln(sprintf('Super admin user is created'));

        $translator      = $container->get('translator');
        $mailer          = $container->get('pum.mailer');
        $mailer
            ->subject($translator->trans('pum.users.register.subject', array(), 'pum'))
            ->from('no-reply@kitae.fr')
            ->to($email)
            ->template('PumCoreBundle:User:Mail/register.html.twig', array(
                'user' => $user,
                'pwd'  => $pwd
            ))
        ;

        if ($result = $mailer->send()) {
            $output->writeln(sprintf('Email with your password sent'));
        } else {
            $output->writeln(sprintf('An error occured while sending your password by email'));
        }
    }

    protected function createPwd($length = 6)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $password = substr(str_shuffle($chars), 0, $length);

        return $password;
    }
}
