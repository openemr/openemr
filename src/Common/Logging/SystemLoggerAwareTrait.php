<?php

namespace OpenEMR\Common\Logging;

use Monolog\Level;
use Psr\Log\{
    LoggerAwareTrait,
    LoggerInterface,
};

trait SystemLoggerAwareTrait
{
    use LoggerAwareTrait;

    public function setSystemLogger(LoggerInterface $logger): void
    {
        $this->setLogger($logger);
    }

    /**
     * @ deprecated read the logger directly
     */
    public function getSystemLogger(?Level $defaultLoggingLevel = null): LoggerInterface
    {
        if ($this->logger === null) {
            $this->logger = new SystemLogger($defaultLoggingLevel);
        }

        return $this->logger;
    }
}
