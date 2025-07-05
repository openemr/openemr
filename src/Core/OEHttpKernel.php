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


    public function getEventDispatcher() : ?\Symfony\Component\EventDispatcher\EventDispatcherInterface{
        return $this->dispatcher;
    }
    public function getSystemLogger() : ?SystemLogger {
        if (empty($this->logger)) {
            $this->logger = new SystemLogger();
        }
        return $this->logger;
    }
    public function setSystemLogger(SystemLogger $logger) : void {
        $this->logger = $logger;
    }
}
