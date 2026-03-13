<?php

namespace OpenEMR\Common\Logging;

use Monolog\Level;
use Psr\Log\{
    LoggerAwareTrait,
    LoggerInterface,
};

/**
 * @deprecated Prefer constructor injection of a logger; use PSR's LoggerAwareTrait if unavoidable.
 */
trait SystemLoggerAwareTrait
{
    use LoggerAwareTrait;

    /**
     * @deprecated use setLogger()
     */
    public function setSystemLogger(LoggerInterface $logger): void
    {
        $this->setLogger($logger);
    }

    /**
     * @deprecated read from the ->logger property
     */
    public function getSystemLogger(?Level $defaultLoggingLevel = null): LoggerInterface
    {
        if ($this->logger === null) {
            $this->logger = new SystemLogger($defaultLoggingLevel);
        }

        return $this->logger;
    }
}
