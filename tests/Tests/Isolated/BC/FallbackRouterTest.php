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

use GuzzleHttp\Psr7\Uri;
use OpenEMR\BC\FallbackRouter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Small]
class FallbackRouterTest extends TestCase
{
    private static function getInstallRoot(): string
    {
        return dirname(__DIR__, 4);
    }

    private function createRequest(string $path, string $query = ''): ServerRequestInterface
    {
        $uri = (new Uri($path))->withQuery($query);

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        return $request;
    }

    /**
     * Paths that MUST be blocked. Uses real files from the repo.
     *
     * @return array<string, array{0: string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
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
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function allowedPathProvider(): array
    {
        return [
            'interface globals' => ['/interface/globals.php'],
            'interface logout' => ['/interface/logout.php'],
            'portal index' => ['/portal/index.php'],
            'portal get_patient_info' => ['/portal/get_patient_info.php'],
        ];
    }

    /**
     * @return array<string, array{0: string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function staticAssetPathProvider(): array
    {
        return [
            'static css' => ['/public/assets/modified/dygraphs-2-0-0/dygraph.css'],
            'static js' => ['/public/assets/modified/dygraphs-2-0-0/dygraph.js'],
            'image' => ['/public/images/login-logo-svg'],
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

        // Blocked paths should 404 (NOT 403)
        $this->expectException(NotFoundHttpException::class);
        $router->performLegacyRouting($this->createRequest($requestUri));
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

    #[DataProvider('staticAssetPathProvider')]
    public function testStaticAssetPaths(string $requestUri): void
    {
        $installRoot = self::getInstallRoot();
        $fullPath = $installRoot . $requestUri;
        if (!file_exists($fullPath)) {
            self::markTestSkipped("Test file does not exist: $requestUri");
        }

        $router = new FallbackRouter($installRoot, new NullLogger());
        $result = $router->performLegacyRouting($this->createRequest($requestUri));

        self::assertNull($result, "Path $requestUri should route to null");
    }

    public function testPathTraversalIsBlocked(): void
    {
        $installRoot = self::getInstallRoot();
        $router = new FallbackRouter($installRoot, new NullLogger());

        // Path traversal to non-existent file throws NotFoundHttpException
        $this->expectException(NotFoundHttpException::class);
        $router->performLegacyRouting($this->createRequest('/interface/../../../etc/passwd'));
    }

    public function testNonexistentPathThrowsNotFound(): void
    {
        $installRoot = self::getInstallRoot();
        $router = new FallbackRouter($installRoot, new NullLogger());

        $this->expectException(NotFoundHttpException::class);
        $router->performLegacyRouting($this->createRequest('/does/not/exist.php'));
    }

    public function testDirectoryWithoutTrailingSlashRedirects(): void
    {
        $installRoot = self::getInstallRoot();
        $router = new FallbackRouter($installRoot, new NullLogger());

        try {
            $router->performLegacyRouting($this->createRequest('/portal'));
            self::fail('Expected HttpException for redirect');
        } catch (HttpException $e) {
            self::assertSame(301, $e->getStatusCode());
            self::assertSame('/portal/', $e->getHeaders()['Location']);
        }
    }

    public function testDirectoryRedirectPreservesQueryString(): void
    {
        $installRoot = self::getInstallRoot();
        $router = new FallbackRouter($installRoot, new NullLogger());

        try {
            $router->performLegacyRouting($this->createRequest('/portal', 'foo=bar'));
            self::fail('Expected HttpException for redirect');
        } catch (HttpException $e) {
            self::assertSame(301, $e->getStatusCode());
            self::assertSame('/portal/?foo=bar', $e->getHeaders()['Location']);
        }
    }

    public function testDirectoryWithoutIndexPhpThrowsNotFound(): void
    {
        $installRoot = self::getInstallRoot();
        $router = new FallbackRouter($installRoot, new NullLogger());

        // /src exists but has no index.php
        $this->expectException(NotFoundHttpException::class);
        $router->performLegacyRouting($this->createRequest('/ci'));
    }

    /**
     * Tests that requests are rewritten to the correct entry point.
     *
     * @return array<string, array{0: string, 1: string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
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

    public function testRootPathRoutesToIndexPhp(): void
    {
        $installRoot = self::getInstallRoot();
        $router = new FallbackRouter($installRoot, new NullLogger());

        $result = $router->performLegacyRouting($this->createRequest('/'));

        self::assertSame($installRoot . '/index.php', $result);
    }

    public function testDoubleSlashesNormalized(): void
    {
        $installRoot = self::getInstallRoot();
        $router = new FallbackRouter($installRoot, new NullLogger());

        // Double slashes should be normalized by realpath
        $result = $router->performLegacyRouting($this->createRequest('/portal//index.php'));

        self::assertSame($installRoot . '/portal/index.php', $result);
    }

    public function testPathTraversalWithinBoundsWorks(): void
    {
        $installRoot = self::getInstallRoot();
        $router = new FallbackRouter($installRoot, new NullLogger());

        // Traversal that stays within install root should resolve
        $result = $router->performLegacyRouting($this->createRequest('/portal/../interface/globals.php'));

        self::assertSame($installRoot . '/interface/globals.php', $result);
    }

    public function testBlockedDirectoryWithoutIndexPhpThrowsNotFound(): void
    {
        $installRoot = self::getInstallRoot();
        $router = new FallbackRouter($installRoot, new NullLogger());

        // /portal/patient/fwk/libs is blocked AND has no index.php
        $this->expectException(NotFoundHttpException::class);
        $router->performLegacyRouting($this->createRequest('/portal/patient/fwk/libs'));
    }

    public function testRewriteRulePathWithoutSlashRoutesCorrectly(): void
    {
        $installRoot = self::getInstallRoot();
        $router = new FallbackRouter($installRoot, new NullLogger());

        // /apis is a rewrite rule, not a directory check
        $result = $router->performLegacyRouting($this->createRequest('/apis'));

        self::assertSame($installRoot . '/apis/dispatch.php', $result);
    }

    public function testDirectoryWithoutIndexPhpAndNoRewriteThrowsNotFound(): void
    {
        $installRoot = self::getInstallRoot();
        $router = new FallbackRouter($installRoot, new NullLogger());

        // /interface exists but has no index.php and no rewrite rule
        $this->expectException(NotFoundHttpException::class);
        $router->performLegacyRouting($this->createRequest('/interface'));
    }
}
