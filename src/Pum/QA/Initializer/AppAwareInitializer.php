<?php

namespace Pum\QA\Initializer;

use Behat\Behat\Context\ContextInterface;
use Behat\Behat\Context\Initializer\InitializerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AppAwareInitializer implements InitializerInterface
{
    private $kernelFactory;
    private $container;

    public function __construct($appDir)
    {
        $this->appDir = $appDir;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ContextInterface $context)
    {
        return $context instanceof AppAwareInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ContextInterface $context)
    {
        $ctx = $this;
        $context->setRunCallback(function($callback) use ($ctx) {
            $ctx->run($callback);
        });
    }

    public function run($callback)
    {
        require_once $this->appDir.'/AppKernel.php';

        $exception = null;
        $app = new \AppKernel('prod', false);
        $app->boot();
        try {
            $result = $callback($app->getContainer());
        } catch (\Exception $e) {
            $exception = $e;
        }

        $app->shutdown();

        if ($exception) {
            throw $exception;
        }

        return $result;
    }
}
