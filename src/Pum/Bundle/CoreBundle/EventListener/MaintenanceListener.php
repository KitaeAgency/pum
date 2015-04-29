<?php

namespace Pum\Bundle\CoreBundle\EventListener;

use Pum\Core\Config\ConfigInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Pum\Bundle\AppBundle\Entity\User;

class MaintenanceListener
{
    private $config;
    private $tokenStorage;
    private $twigEngine;

    private $isUnderMaintenance = false;
    private $template;
    private $whiteIps;
    private $whitePaths;

    public function __construct(ConfigInterface $config, TokenStorage $tokenStorage, TwigEngine $twigEngine, $template, array $whiteIps, array $whitePaths)
    {
        $this->config = $config;
        $this->tokenStorage = $tokenStorage;
        $this->twigEngine = $twigEngine;

        $this->template = $template;

        $this->whiteIps = array_unique(array_merge($whiteIps, $this->config->get('maintenance_restriction_ips', array())));
        $this->whitePaths = array_unique(array_merge($whitePaths, array('_profiler', '_wdt', 'pum-login', 'pum-login-check', 'js', 'css', 'images')));
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->isUnderMaintenance = $this->config->get('maintenance_mode', false);

        if ($this->isUnderMaintenance) {
            $clientIp = $event->getRequest()->getClientIp();

            // is IP authorized
            if (!empty($whiteIps)) {
                foreach ($whiteIps as $ip) {
                    if ($whiteIps === $clientIp) {
                        return true;
                    }
                }
            }

            // is User connected and authorized
            $token = $this->tokenStorage->getToken();
            if ($token != null) {
                $user = $token->getUser();
                if ($user instanceof User && $user->getGroup()->isAdmin()) {
                    return true;
                }
            }

            // is Path authorized
            $path = trim($event->getRequest()->getPathInfo(), '/');
            foreach ($this->whitePaths as $pattern) {
                if (preg_match('/^' . $pattern . '/', $path)) {
                    return true;
                }
            }

            $event->setResponse(new Response($this->twigEngine->render($this->template), 503));
            $event->stopPropagation();
        }
    }
}
