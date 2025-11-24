<?php

namespace OpenEMR\Core;

use OpenEMR\Common\Logging\SystemLogger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OEHttpKernel extends HttpKernel
{
    private SystemLogger $logger;

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

    public function getEventDispatcher(): ?\Symfony\Component\EventDispatcher\EventDispatcherInterface
    {
        return $this->dispatcher;
    }
    public function getSystemLogger(): ?SystemLogger
    {
        if (empty($this->logger)) {
            $this->logger = new SystemLogger();
        }
        return $this->logger;
    }
    public function setSystemLogger(SystemLogger $logger): void
    {
        $this->logger = $logger;
    }
}
