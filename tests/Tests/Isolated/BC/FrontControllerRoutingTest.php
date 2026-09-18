<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BC;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * E2E tests for front controller routing using PHP's built-in server.
 *
 * These tests validate that requests are routed to the correct entry point.
 * They do NOT validate full application behavior (which requires a database).
 */
#[Large]
class FrontControllerRoutingTest extends TestCase
{
    private static Process $process;
    private static Client $http;

    public static function setUpBeforeClass(): void
    {
        $docRoot = dirname(__DIR__, 4);
        $router = $docRoot . '/public/index.php';

        $host = '127.0.0.1';
        $port = 8765;

        self::$process = new Process([
            'php',
            '-S', sprintf('%s:%d', $host, $port),
            $router,
        ]);
        self::$process->start();

        // Give the server time to start
        usleep(500_000);

        self::$http = new Client([
            'base_uri' => sprintf('http://%s:%d', $host, $port),
            'http_errors' => false,
            'timeout' => 10,
        ]);
    }

    public static function tearDownAfterClass(): void
    {
        self::$process->stop();
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function blockedPathProvider(): array
    {
        return [
            'src directory' => ['/src/BC/FallbackRouter.php'],
            'vendor directory' => ['/vendor/autoload.php'],
            'config directory' => ['/config/services.php'],
            'dotfile' => ['/.env'],
        ];
    }

    #[DataProvider('blockedPathProvider')]
    public function testBlockedPathReturns404(string $path): void
    {
        $response = self::$http->get($path);

        self::assertSame(
            404,
            $response->getStatusCode(),
            "Path $path should be blocked (404), got " . $response->getStatusCode(),
        );
    }

    /**
     * Routes that should return 418 from the test endpoint, proving
     * the request reached the correct entry point file.
     *
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function routingTestProvider(): array
    {
        return [
            'apis' => ['/apis/default/_routing_test', 'apis'],
            'oauth2' => ['/oauth2/default/_routing_test', 'oauth2'],
        ];
    }

    #[DataProvider('routingTestProvider')]
    public function testRoutingReachesEntryPoint(string $path, string $expectedEntryPoint): void
    {
        $response = self::$http->get($path);
        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        self::assertSame(
            418,
            $status,
            "Path $path should return 418, got $status. Body: $body",
        );

        $decoded = json_decode($body, true);
        self::assertSame(
            ['routed' => $expectedEntryPoint],
            $decoded,
        );
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function trailingSlashRedirectProvider(): array
    {
        return [
            'portal' => ['/portal', '/portal/'],
            'portal with qs' => ['/portal?foo=bar', '/portal/?foo=bar'],
        ];
    }

    #[DataProvider('trailingSlashRedirectProvider')]
    public function testDirectoryWithoutSlashRedirects(string $path, string $expectedLocation): void
    {
        $response = self::$http->get($path, ['allow_redirects' => false]);

        self::assertSame(301, $response->getStatusCode());
        // The redirect may or may not include the host; not part of the
        // testing spec.
        $uri = new Uri($response->getHeaderLine('Location'));
        $expectedUri = new Uri($expectedLocation);
        self::assertSame($expectedUri->getPath(), $uri->getPath());
        self::assertSame($expectedUri->getQuery(), $uri->getQuery());
    }
}
