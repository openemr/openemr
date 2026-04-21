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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\NullLogger;

use const E_DEPRECATED;
use const E_USER_WARNING;

#[Group('isolated')]
#[Group('core')]
class ErrorHandlerTest extends TestCase
{
    private function createHandler(bool $shouldDisplayErrors = false): ErrorHandler
    {
        return new ErrorHandler(
            new NullLogger(),
            $this->createStub(ResponseFactoryInterface::class),
            $this->createStub(StreamFactoryInterface::class),
            $shouldDisplayErrors,
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

    // handleException() cannot be unit tested because it calls exit() which
    // terminates the process. Tests run in CLI mode which hits exit(1)
    // immediately after logging.
}
