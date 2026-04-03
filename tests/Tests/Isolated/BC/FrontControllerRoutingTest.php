<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BC;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OpenEMR\BC\FallbackRouter;
use PHPUnit\Framework\Attributes\CoversClass;
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
#[CoversClass(FallbackRouter::class)]
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
            // docroot?
            $router,
        ]);
        self::$process->start();

        // if (!is_resource(self::$serverProcess)) {
        //     self::fail('Failed to start PHP built-in server');
        // }

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
     * Routes that should be handled (not 404).
     * We don't assert on exact status since many require a database.
     *
     * @return array<string, array{0: string}>
     */
    public static function routedPathProvider(): array
    {
        return [
            // 'apis fhir metadata' => ['/apis/default/fhir/metadata'],
            // 'oauth2 well-known' => ['/oauth2/default/.well-known/openid-configuration'],
        ];
    }

    #[DataProvider('routedPathProvider')]
    public function testPathIsRouted(string $path): void
    {
        $response = self::$http->get($path);
        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        var_dump($body);

        // Should not be 404 - that would mean routing failed
        self::assertNotSame(
            404,
            $status,
            "Path $path returned 404 - routing failed",
        );

        // Should return JSON (even error responses)
        $decoded = json_decode($body, true);
        self::assertNotNull(
            $decoded,
            "Path $path did not return valid JSON. Status: $status, Body: $body",
        );
    }

    /**
     * @return array<string, array{0: string}>
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
     * @return array<string, array{0: string, 1: string}>
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
}
