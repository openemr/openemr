<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BC;

use OpenEMR\BC\FallbackRouter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(FallbackRouter::class)]
#[Small]
class FallbackRouterTest extends TestCase
{
    private static function getInstallRoot(): string
    {
        return dirname(__DIR__, 4);
    }

    /**
     * Paths that MUST be blocked. Uses real files from the repo.
     *
     * @return array<string, array{0: string}>
     */
    public static function blockedPathProvider(): array
    {
        return [
            // Dotfiles
            '.htaccess' => ['/.htaccess'],
            '.gitignore' => ['/.gitignore'],
            '.git/config' => ['/.git/config'],

            // src directory
            'src class' => ['/src/BC/FallbackRouter.php'],

            // tests directory
            'tests' => ['/tests/Tests/Isolated/BC/FallbackRouterTest.php'],

            // vendor directory
            'vendor autoload' => ['/vendor/autoload.php'],
            'vendor file' => ['/vendor/composer/autoload_psr4.php'],

            // config directory
            'config services' => ['/config/services.php'],

            // bin directory (bin/.htaccess: Deny From All)
            'bin' => ['/bin/console'],

            // sites documents (sites/*/documents/.htaccess: Deny From All)
            'sites documents htaccess' => ['/sites/default/documents/.htaccess'],

            // portal framework libs (portal/patient/fwk/libs/.htaccess: deny from all)
            'portal fwk libs htaccess' => ['/portal/patient/fwk/libs/.htaccess'],

            // .inc.php files
            'inc.php file' => ['/library/sql.inc.php'],

            // sqlconf.php
            'sqlconf' => ['/sites/default/sqlconf.php'],

            // templates
            'templates' => ['/templates/core/base.html.twig'],
        ];
    }

    /**
     * Paths that MUST be allowed. Uses real files from the repo.
     *
     * @return array<string, array{0: string}>
     */
    public static function allowedPathProvider(): array
    {
        return [
            'interface globals' => ['/interface/globals.php'],
            'interface logout' => ['/interface/logout.php'],
            'portal index' => ['/portal/index.php'],
            'static css' => ['/public/assets/modified/dygraphs-2-0-0/dygraph.css'],
            'static js' => ['/public/assets/modified/dygraphs-2-0-0/dygraph.js'],
        ];
    }

    #[DataProvider('blockedPathProvider')]
    public function testBlockedPaths(string $requestUri): void
    {
        $installRoot = self::getInstallRoot();
        $fullPath = $installRoot . $requestUri;
        if (!file_exists($fullPath)) {
            self::markTestSkipped("Test file does not exist: $requestUri");
        }

        $router = new FallbackRouter($installRoot, new NullLogger());
        $result = $router->performLegacyRouting($requestUri);

        self::assertNull($result, "Path $requestUri should be blocked");
    }

    #[DataProvider('allowedPathProvider')]
    public function testAllowedPaths(string $requestUri): void
    {
        $installRoot = self::getInstallRoot();
        $fullPath = $installRoot . $requestUri;
        if (!file_exists($fullPath)) {
            self::markTestSkipped("Test file does not exist: $requestUri");
        }

        $router = new FallbackRouter($installRoot, new NullLogger());
        $result = $router->performLegacyRouting($requestUri);

        self::assertSame($fullPath, $result, "Path $requestUri should be allowed");
    }

    public function testPathTraversalIsBlocked(): void
    {
        $installRoot = self::getInstallRoot();
        $router = new FallbackRouter($installRoot, new NullLogger());

        $result = $router->performLegacyRouting('/interface/../../../etc/passwd');

        self::assertNull($result);
    }

    public function testNonexistentPathReturnsNull(): void
    {
        $installRoot = self::getInstallRoot();
        $router = new FallbackRouter($installRoot, new NullLogger());

        $result = $router->performLegacyRouting('/does/not/exist.php');

        self::assertNull($result);
    }
}
