<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Core;

use ErrorException;
use OpenEMR\Core\ErrorHandler;
use OpenEMR\Core\ErrorHandlingMode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Stringable;

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

#[Group('isolated')]
#[Group('core')]
class ErrorHandlerTest extends TestCase
{
    private function createHandler(
        bool $shouldDisplayErrors = false,
        ErrorHandlingMode $errorMode = ErrorHandlingMode::Throw,
        ?LoggerInterface $logger = null,
    ): ErrorHandler {
        return new ErrorHandler(
            logger: $logger ?? new NullLogger(),
            rf: $this->createStub(ResponseFactoryInterface::class),
            sf: $this->createStub(StreamFactoryInterface::class),
            errorMode: $errorMode,
            shouldDisplayErrors: $shouldDisplayErrors,
        );
    }

    public function testHandleErrorThrowsWhenErrorReportingMatches(): void
    {
        $handler = $this->createHandler();

        // Ensure E_USER_WARNING is in error_reporting
        $originalLevel = error_reporting(E_ALL);
        try {
            $this->expectException(ErrorException::class);
            $this->expectExceptionMessage('Test error');

            $handler->handleError(E_USER_WARNING, 'Test error', '/path/to/file.php', 42);
        } finally {
            error_reporting($originalLevel);
        }
    }

    public function testHandleErrorReturnsFalseWhenSuppressed(): void
    {
        $handler = $this->createHandler();

        // The @ operator sets error_reporting to 0 for the duration of the expression
        $result = @$handler->handleError(E_USER_WARNING, 'Suppressed error', '/path/to/file.php', 42);

        self::assertFalse($result);
    }

    public function testDeprecationThrowsEvenWhenSuppressed(): void
    {
        // PHPUNIT_COMPOSER_INSTALL is defined during test runs, so deprecations
        // always throw regardless of error_reporting level
        $handler = $this->createHandler();

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Deprecated function');

        @$handler->handleError(E_DEPRECATED, 'Deprecated function', '/path/to/file.php', 50);
    }

    public function testErrorHandlerInstallation(): void
    {
        $handler = $this->createHandler();
        $handler->installErrorHandler(E_ALL);
        $old = error_reporting(E_ALL);

        try {
            trigger_error('test error', E_USER_WARNING);
            self::fail('Error handler did not trigger');
        } catch (ErrorException $e) {
            self::assertSame('test error', $e->getMessage());
            self::assertSame(E_USER_WARNING, $e->getSeverity());
        } finally {
            restore_error_handler();
            error_reporting($old);
        }
    }

    public function testHandleErrorLogsInsteadOfThrowsWhenModeIsLog(): void
    {
        $logger = new class extends AbstractLogger {
            /** @var list<array{level: mixed, message: string|Stringable, context: array<mixed>}> */
            public array $records = [];

            public function log($level, string|Stringable $message, array $context = []): void
            {
                $this->records[] = ['level' => $level, 'message' => $message, 'context' => $context];
            }
        };
        $handler = $this->createHandler(errorMode: ErrorHandlingMode::Log, logger: $logger);

        $originalLevel = error_reporting(E_ALL);
        try {
            $result = $handler->handleError(E_USER_WARNING, 'Soft warning', '/path/to/file.php', 42);
        } finally {
            error_reporting($originalLevel);
        }

        self::assertTrue($result, 'Log mode should report the error as handled');
        self::assertCount(1, $logger->records, 'Log mode should emit exactly one record for the handled error');
        self::assertSame(LogLevel::WARNING, $logger->records[0]['level'], 'E_USER_WARNING should map to LogLevel::WARNING');
        self::assertSame('Soft warning', $logger->records[0]['context']['message'], 'Original message goes into context');
        self::assertSame(E_USER_WARNING, $logger->records[0]['context']['errno'], 'errno preserved in context for downstream filters');
        self::assertSame('/path/to/file.php', $logger->records[0]['context']['file'], 'file preserved in context');
        self::assertSame(42, $logger->records[0]['context']['line'], 'line preserved in context');
    }

    /**
     * @param LogLevel::* $expectedLevel
     */
    #[DataProvider('errorLevelMapProvider')]
    public function testHandleErrorMapsPhpSeverityToPsrLevel(int $errno, string $expectedLevel): void
    {
        $logger = new class extends AbstractLogger {
            /** @var list<array{level: mixed, message: string|Stringable, context: array<mixed>}> */
            public array $records = [];

            public function log($level, string|Stringable $message, array $context = []): void
            {
                $this->records[] = ['level' => $level, 'message' => $message, 'context' => $context];
            }
        };
        $handler = $this->createHandler(errorMode: ErrorHandlingMode::Log, logger: $logger);

        $originalLevel = error_reporting(E_ALL);
        try {
            $handler->handleError($errno, 'msg', '/f.php', 1);
        } finally {
            error_reporting($originalLevel);
        }

        self::assertSame($expectedLevel, $logger->records[0]['level'], 'PHP severity should map to the ported Monolog PSR level');
    }

    /**
     * @return array<string, array{int, LogLevel::*}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function errorLevelMapProvider(): array
    {
        return [
            'E_ERROR'             => [E_ERROR, LogLevel::CRITICAL],
            'E_WARNING'           => [E_WARNING, LogLevel::WARNING],
            'E_PARSE'             => [E_PARSE, LogLevel::ALERT],
            'E_NOTICE'            => [E_NOTICE, LogLevel::NOTICE],
            'E_CORE_ERROR'        => [E_CORE_ERROR, LogLevel::CRITICAL],
            'E_CORE_WARNING'      => [E_CORE_WARNING, LogLevel::WARNING],
            'E_COMPILE_ERROR'     => [E_COMPILE_ERROR, LogLevel::ALERT],
            'E_COMPILE_WARNING'   => [E_COMPILE_WARNING, LogLevel::WARNING],
            'E_USER_ERROR'        => [E_USER_ERROR, LogLevel::ERROR],
            'E_USER_WARNING'      => [E_USER_WARNING, LogLevel::WARNING],
            'E_USER_NOTICE'       => [E_USER_NOTICE, LogLevel::NOTICE],
            'E_RECOVERABLE_ERROR' => [E_RECOVERABLE_ERROR, LogLevel::ERROR],
            'E_DEPRECATED'        => [E_DEPRECATED, LogLevel::NOTICE],
            'E_USER_DEPRECATED'   => [E_USER_DEPRECATED, LogLevel::NOTICE],
        ];
    }

    // handleException() cannot be unit tested because it calls exit() which
    // terminates the process. Tests run in CLI mode which hits exit(1)
    // immediately after logging.
}
