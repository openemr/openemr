<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core;

use ErrorException;
use Psr\Http\Message\{
    ResponseFactoryInterface,
    ResponseInterface,
    StreamFactoryInterface,
};
use Psr\Log\{LoggerInterface, LogLevel};
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

use function defined;
use function error_reporting;
use function header;
use function http_response_code;
use function is_int;
use function is_string;
use function set_error_handler;
use function set_exception_handler;
use function sprintf;

use const E_COMPILE_ERROR;
use const E_COMPILE_WARNING;
use const E_CORE_ERROR;
use const E_CORE_WARNING;
use const E_DEPRECATED;
use const E_ERROR;
use const E_NOTICE;
use const E_PARSE;
use const E_RECOVERABLE_ERROR;
use const E_USER_DEPRECATED;
use const E_USER_ERROR;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use const E_WARNING;

readonly class ErrorHandler
{
    private bool $isCli;

    public function __construct(
        private LoggerInterface $logger,
        private ResponseFactoryInterface $rf,
        private StreamFactoryInterface $sf,
        private ErrorHandlingMode $errorMode,
        private bool $shouldDisplayErrors,
    ) {
        $this->isCli = (PHP_SAPI === 'cli');
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
        // severity, handle the error in the configured manner. With this check,
        // `@` error suppression (or changes to `error_reporting`) are respected.
        if ((error_reporting() & $errno) !== 0) {
            match ($this->errorMode) {
                ErrorHandlingMode::Log => $this->logger->log(
                    self::pickErrorLevel($errno),
                    'PHP error: {message} ({file}:{line})',
                    [
                        'errno' => $errno,
                        'message' => $errstr,
                        'file' => $errfile,
                        'line' => $errline,
                    ],
                ),
                ErrorHandlingMode::Throw => throw new ErrorException($errstr, 0, $errno, $errfile, $errline),
            };
            // Indicates this has been handled.
            return true;
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
        $this->logUncaughtException($e);

        // CLI scripts simply exit nonzero
        if ($this->isCli) {
            exit(1);
        }

        $response = $this->buildResponse($e);

        if (!headers_sent()) {
            http_response_code($response->getStatusCode());
            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }

        echo (string)$response->getBody();
        exit();
    }

    private function buildResponse(Throwable $e): ResponseInterface
    {
        // Future scope: create a body based on request context (e.g. accept
        // headers, etc).
        if ($e instanceof HttpExceptionInterface) {
            $response = $this->rf->createResponse($e->getStatusCode());
            foreach ($e->getHeaders() as $header => $value) {
                $response = $response->withAddedHeader($header, $this->normalizeHeaderValue($header, $value));
            }
            return $response;
        }

        // Default: nondescript 500 error.
        $message = 'An error has occurred.';
        if ($this->shouldDisplayErrors) {
            $message .= "\n$e";
        }

        return $this->rf->createResponse(500)
            ->withBody($this->sf->createStream($message));
    }

    /**
     * @return string|list<string>
     */
    private function normalizeHeaderValue(string $header, mixed $value): string|array
    {
        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $entry) {
                if (!is_string($entry) && !is_int($entry)) {
                    throw new \UnexpectedValueException(sprintf(
                        'HttpExceptionInterface header "%s" array entry must be string or int; got %s',
                        $header,
                        get_debug_type($entry),
                    ));
                }
                $normalized[] = (string)$entry;
            }
            return $normalized;
        }
        if (is_string($value) || is_int($value)) {
            return (string)$value;
        }
        throw new \UnexpectedValueException(sprintf(
            'HttpExceptionInterface header "%s" must be string, int, or array; got %s',
            $header,
            get_debug_type($value),
        ));
    }

    private function logUncaughtException(Throwable $e): void
    {
        if ($e instanceof HttpExceptionInterface) {
            $code = $e->getStatusCode();
            $logLevel = match (true) {
                $code < 300 => LogLevel::DEBUG,
                $code < 400 => LogLevel::INFO,
                $code < 500 => LogLevel::NOTICE,
                default => LogLevel::WARNING,
            };
            $this->logger->log($logLevel, 'Caught {type} with code {code}', [
                'type' => $e::class,
                'code' => $code,
            ]);
            return;
        }

        // Fallback: log everything else at a high severity. This will very
        // likely get toned down over time.
        $this->logger->critical('Uncaught exception!', [
            'exception' => $e,
        ]);
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

    /**
     * Ported from Monolog\ErrorHandler::defaultErrorLevelMap(). The literal
     * 2048 is E_STRICT, which is removed in PHP 8.4 but retained here so
     * lookups still resolve if a legacy caller passes the raw int.
     *
     * @return LogLevel::*
     */
    private static function pickErrorLevel(int $phpErrorLevel): string
    {
        return match ($phpErrorLevel) {
            E_ERROR, E_CORE_ERROR => LogLevel::CRITICAL,
            E_PARSE, E_COMPILE_ERROR => LogLevel::ALERT,
            E_USER_ERROR, E_RECOVERABLE_ERROR => LogLevel::ERROR,
            E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING => LogLevel::WARNING,
            E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED, 2048 => LogLevel::NOTICE,
            default => LogLevel::CRITICAL,
        };
    }
}
