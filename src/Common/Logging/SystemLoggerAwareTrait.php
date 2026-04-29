<?php

namespace OpenEMR\Common\Logging;

use Psr\Log\{
    LoggerAwareTrait,
    LoggerInterface,
};
use OpenEMR\BC\ServiceContainer;

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
    public function getSystemLogger(): LoggerInterface
    {
        if ($this->logger === null) {
            $this->logger = ServiceContainer::getLogger();
        }

        return $this->logger;
    }
}
