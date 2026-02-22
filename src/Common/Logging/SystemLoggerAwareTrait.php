<?php

namespace OpenEMR\Common\Logging;

use Monolog\Level;
use Psr\Log\LoggerInterface;

// TODO: This trait uses concrete SystemLogger and Monolog\Level types instead of
// Psr\Log\LoggerInterface and PSR-3 log level strings. To fully decouple from
// concrete implementations:
// 1. Change $defaultLoggingLevel to ?string and accept Psr\Log\LogLevel constants
// 2. Return LoggerInterface instead of SystemLogger
// 3. Remove lazy instantiation (require logger injection) or use a factory/container
// See SystemLogger for related changes needed there.
trait SystemLoggerAwareTrait
{
    protected SystemLogger|LoggerInterface|null $systemLogger = null;

    /**
     * Set the logger instance.
     *
     * Note: If using custom SystemLogger methods like errorLogCaller(), callers
     * should verify the logger type at runtime. Use a NullLogger or other PSR-3
     * implementation to suppress logging in tests.
     */
    public function setSystemLogger(SystemLogger|LoggerInterface $systemLogger): void
    {
        $this->systemLogger = $systemLogger;
    }

    public function getSystemLogger(?Level $defaultLoggingLevel = null): SystemLogger|LoggerInterface
    {

        if (!isset($this->systemLogger)) {
            $this->systemLogger = new SystemLogger($defaultLoggingLevel);
        }

        return $this->systemLogger;
    }
}
