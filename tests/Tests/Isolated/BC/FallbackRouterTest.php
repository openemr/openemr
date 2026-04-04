<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BC;

use OpenEMR\BC\FallbackRouter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\NullLogger;

#[CoversClass(FallbackRouter::class)]
#[Small]
class FallbackRouterTest extends TestCase
{
    private static function getInstallRoot(): string
    {
        return dirname(__DIR__, 4);
    }

    private function createRequest(string $path): ServerRequestInterface
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn($path);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        return $request;
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

            // ci directory
            'ci' => ['/ci/nginx/nginx.conf'],

            // config directory
            'config services' => ['/config/services.php'],

            // docker directory
            'docker' => ['/docker/README.md'],

            // bin directory (bin/.htaccess: Deny From All)
            'bin' => ['/bin/console'],

            // Package manager files (blocked anywhere)
            'composer.json' => ['/composer.json'],
            'composer.lock' => ['/composer.lock'],
            'package.json' => ['/package.json'],
            'package-lock.json' => ['/package-lock.json'],
            'nested package.json' => ['/ccdaservice/package.json'],

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
        $result = $router->performLegacyRouting($this->createRequest($requestUri));

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
        $result = $router->performLegacyRouting($this->createRequest($requestUri));

        self::assertSame($fullPath, $result, "Path $requestUri should be allowed");
    }

    public function testPathTraversalIsBlocked(): void
    {
        $installRoot = self::getInstallRoot();
        $router = new FallbackRouter($installRoot, new NullLogger());

        $result = $router->performLegacyRouting($this->createRequest('/interface/../../../etc/passwd'));

        self::assertNull($result);
    }

    public function testNonexistentPathReturnsNull(): void
    {
        $installRoot = self::getInstallRoot();
        $router = new FallbackRouter($installRoot, new NullLogger());

        $result = $router->performLegacyRouting($this->createRequest('/does/not/exist.php'));

        self::assertNull($result);
    }

    /**
     * Tests that requests are rewritten to the correct entry point.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function rewriteRuleProvider(): array
    {
        return [
            'apis fhir' => ['/apis/default/fhir/metadata', '/apis/dispatch.php'],
            'apis api' => ['/apis/default/api/patient', '/apis/dispatch.php'],
            'oauth2 well-known' => ['/oauth2/default/.well-known/openid-configuration', '/oauth2/authorize.php'],
            'oauth2 authorize' => ['/oauth2/default/authorize', '/oauth2/authorize.php'],
            'portal patient' => ['/portal/patient/home', '/portal/patient/index.php'],
            'portal patient root' => ['/portal/patient/', '/portal/patient/index.php'],
            'zend modules' => ['/interface/modules/zend_modules/public/some-route', '/interface/modules/zend_modules/public/index.php'],
        ];
    }

    #[DataProvider('rewriteRuleProvider')]
    public function testRewriteRules(string $requestUri, string $expectedTarget): void
    {
        $installRoot = self::getInstallRoot();
        $router = new FallbackRouter($installRoot, new NullLogger());

        $result = $router->performLegacyRouting($this->createRequest($requestUri));

        self::assertSame($installRoot . $expectedTarget, $result);
    }
}
