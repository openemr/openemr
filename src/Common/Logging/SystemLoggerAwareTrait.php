<?php

namespace OpenEMR\Common\Logging;

use Monolog\Level;

trait SystemLoggerAwareTrait
{
    protected ?SystemLogger $systemLogger = null;

    public function setSystemLogger(SystemLogger $systemLogger): void
    {
        $this->systemLogger = $systemLogger;
    }

    public function getSystemLogger(?Level $defaultLoggingLevel = null): ?SystemLogger
    {

        if (!isset($this->systemLogger)) {
            $this->systemLogger = new SystemLogger($defaultLoggingLevel);
        }

        return $this->systemLogger;
    }
}
