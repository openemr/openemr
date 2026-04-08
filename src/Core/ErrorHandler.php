<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core;

use ErrorException;
use OpenEMR\BC\ServiceContainer;
use Psr\Log\LoggerInterface;
use Throwable;

use function defined;
use function error_reporting;
use function http_response_code;
use function set_error_handler;
use function set_exception_handler;

use const E_DEPRECATED;
use const E_USER_DEPRECATED;

readonly class ErrorHandler
{
    private LoggerInterface $logger;
    private bool $isCli;

    public function __construct(
        ?LoggerInterface $logger,
        private bool $shouldDisplayErrors = false,
    ) {
        $this->isCli = (PHP_SAPI === 'cli');
        $this->logger = $logger ?? ServiceContainer::getLogger();
    }

    /**
     * Promotes errors to ErrorExceptions, respecting the current
     * error_reporting level (including @)
     *
     * @link https://www.php.net/manual/en/function.set-error-handler.php
     */
    public function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline,
    ): bool {
        // If the current error_reporting error level matches the specified
        // severity, convert it to an error exception. With this check, `@` error
        // suppression (or changes to `error_reporting`) are respected.
        if ((error_reporting() & $errno) !== 0) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }
        // If the current error_reporting DOES NOT capture the error level,
        // still log deprecation warnings even if they're turned off at runtime.
        // If running inside unit tests, throw anyway.
        if ($errno === E_USER_DEPRECATED || $errno === E_DEPRECATED) {
            if (defined('PHPUNIT_COMPOSER_INSTALL')) {
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            }
            $this->logger->warning('Deprecated: {message} ({file}:{line})', [
                'message' => $errstr,
                'file' => $errfile,
                'line' => $errline,
            ]);
        }
        // "If the function returns false then the normal error handler
        // continues."
        return false;
    }

    /**
     * Handler for uncaught exceptions. This will do a few things:
     * - Log information about the exception and stack trace
     * - Set HTTP status codes for web requests
     * - Render some basic information that an error occurred
     * - Abort the request
     */
    public function handleException(Throwable $e): never
    {
        $this->logger->critical('Uncaught exception!', [
            'exception' => $e,
        ]);

        // CLI scripts simply exit nonzero
        if ($this->isCli) {
            exit(1);
        }

        // For now, just crash out with minimal detail as before.
        // In the future, this can be smarter about request context (accept
        // headers), switching on exception type to yield the correct http
        // code, etc.
        // Note: under rare circumstances, this could trigger a secondary error
        // if headers are already sent. The default deployment seems to turn on
        // output_buffering in php.ini automatically so it's unlikely to occur
        http_response_code(500);
        header('Content-type: text/plain');
        echo 'An error has occurred.';

        if ($this->shouldDisplayErrors) {
            echo $e;
        }
        exit();
    }

    /**
     * Configures the runtime with this class's `handleError` as the system
     * error handler.
     */
    public function installErrorHandler(int $level = E_ALL): void
    {
        set_error_handler($this->handleError(...), $level);
    }

    /**
     * Configures the runtime with this class's `handleException` as the system
     * fallback exception handler. Only exceptions that are uncaught will reach
     * it; locally-handled exceptions are not changed.
     */
    public function installExceptionHandler(): void
    {
        set_exception_handler($this->handleException(...));
    }
}
