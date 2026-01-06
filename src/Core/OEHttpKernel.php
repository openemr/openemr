<?php

namespace OpenEMR\Core;

use OpenEMR\Common\Logging\SystemLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\HttpKernel;

class OEHttpKernel extends HttpKernel
{
    private LoggerInterface $logger;

    private readonly OEGlobalsBag $globalsBag;

    public function __construct(EventDispatcherInterface $dispatcher, ControllerResolverInterface $resolver, ?RequestStack $requestStack = null, ?ArgumentResolverInterface $argumentResolver = null, bool $handleAllThrowables = false)
    {
        parent::__construct($dispatcher, $resolver, $requestStack, $argumentResolver, $handleAllThrowables);
        $this->globalsBag = new OEGlobalsBag([], true); // set compatibility mode to true until we can get rid of it
    }

    public function getGlobalsBag(): OEGlobalsBag
    {
        return $this->globalsBag;
    }

    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }
    public function getSystemLogger(): LoggerInterface
    {
        if (empty($this->logger)) {
            $this->logger = new SystemLogger();
        }
        return $this->logger;
    }
    public function setSystemLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
