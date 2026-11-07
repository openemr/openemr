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
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Core\ErrorHandler;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    private function createResponseHandler(bool $shouldDisplayErrors = false): ErrorHandler
    {
        // The production factories, so known codes get their standard reason phrase.
        ServiceContainer::reset();
        return new ErrorHandler(
            new NullLogger(),
            ServiceContainer::getResponseFactory(),
            ServiceContainer::getStreamFactory(),
            $shouldDisplayErrors,
        );
    }

    public function testHttpExceptionResponseHasStatusTextBody(): void
    {
        $response = $this->createResponseHandler()
            ->buildResponse(new NotFoundHttpException('Controller not found: Some\Internal\Controller'));

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('Not Found', (string) $response->getBody());
        self::assertSame('text/plain; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    public function testHttpExceptionResponseReplacesContentType(): void
    {
        $response = $this->createResponseHandler()
            ->buildResponse(new HttpException(404, headers: ['Content-Type' => 'text/html']));

        self::assertSame('text/plain; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    public function testHttpExceptionResponseKeepsHeaders(): void
    {
        $response = $this->createResponseHandler()
            ->buildResponse(new MethodNotAllowedHttpException(['GET', 'POST']));

        self::assertSame(405, $response->getStatusCode());
        self::assertSame('GET, POST', $response->getHeaderLine('Allow'));
        self::assertSame('Method Not Allowed', (string) $response->getBody());
    }

    public function testHttpExceptionResponseDropsStaleContentLength(): void
    {
        $response = $this->createResponseHandler()
            ->buildResponse(new HttpException(404, headers: ['Content-Length' => '0']));

        self::assertFalse($response->hasHeader('Content-Length'));
        self::assertSame('Not Found', (string) $response->getBody());
    }

    public function testHttpExceptionWithUnknownStatusGetsGenericBody(): void
    {
        $response = $this->createResponseHandler()
            ->buildResponse(new HttpException(499, 'Client closed request'));

        self::assertSame(499, $response->getStatusCode());
        self::assertSame('An error has occurred.', (string) $response->getBody());
    }

    public function testHttpExceptionResponseIncludesDetailWhenDisplayingErrors(): void
    {
        $response = $this->createResponseHandler(shouldDisplayErrors: true)
            ->buildResponse(new BadRequestHttpException('Missing pid'));

        self::assertSame(400, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringStartsWith("Bad Request\n", $body);
        self::assertStringContainsString('Missing pid', $body);
    }

    public function testOtherExceptionResponseIsGeneric500(): void
    {
        $response = $this->createResponseHandler()
            ->buildResponse(new RuntimeException('SELECT * FROM secrets'));

        self::assertSame(500, $response->getStatusCode());
        self::assertSame('An error has occurred.', (string) $response->getBody());
        self::assertSame('text/plain; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    public function testHeaderLinesReplaceHeadersAlreadySent(): void
    {
        $handler = $this->createResponseHandler();
        $response = $handler->buildResponse(new MethodNotAllowedHttpException(['GET', 'POST']));

        $lines = $handler->headerLines($response);

        self::assertContains(['line' => 'Content-Type: text/plain; charset=utf-8', 'replace' => true], $lines);
        self::assertContains(['line' => 'Allow: GET, POST', 'replace' => true], $lines);
    }

    public function testHeaderLinesReplaceOnlyWithTheFirstValue(): void
    {
        $handler = $this->createResponseHandler();
        $response = $handler->buildResponse(new HttpException(404))
            ->withAddedHeader('Link', '</a>; rel=preload')
            ->withAddedHeader('Link', '</b>; rel=preload');

        self::assertSame(
            [
                ['line' => 'Link: </a>; rel=preload', 'replace' => true],
                ['line' => 'Link: </b>; rel=preload', 'replace' => false],
            ],
            array_values(array_filter(
                $handler->headerLines($response),
                static fn(array $header): bool => str_starts_with($header['line'], 'Link: '),
            )),
        );
    }

    public function testHeaderLinesKeepEarlierCookies(): void
    {
        $handler = $this->createResponseHandler();
        $response = $handler->buildResponse(new HttpException(404, headers: ['Set-Cookie' => 'a=1']));

        self::assertContains(['line' => 'Set-Cookie: a=1', 'replace' => false], $handler->headerLines($response));
    }

    // handleException() cannot be unit tested because it calls exit() which
    // terminates the process. Tests run in CLI mode which hits exit(1)
    // immediately after logging.
}
