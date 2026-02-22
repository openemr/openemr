<?php

namespace OpenEMR\Common\Logging;

use Monolog\Level;

// TODO: This trait uses concrete SystemLogger and Monolog\Level types instead of
// Psr\Log\LoggerInterface and PSR-3 log level strings. To fully decouple from
// concrete implementations:
// 1. Change $defaultLoggingLevel to ?string and accept Psr\Log\LogLevel constants
// 2. Return LoggerInterface instead of SystemLogger
// 3. Remove lazy instantiation (require logger injection) or use a factory/container
// See SystemLogger for related changes needed there.
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
