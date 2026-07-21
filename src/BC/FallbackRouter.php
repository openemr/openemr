<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\BC;

use OutOfRangeException;
use Psr\Http\Message\{
    ServerRequestInterface,
    UriInterface,
};
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function str_starts_with;

/**
 * Sets up the application for "legacy" routing, i.e. as if the request was
 * being processed directly by the target file.
 *
 * Performs some basic security checks for invalid paths, but largely doesn't
 * aim to do much that wasn't the case previously. The goal is to enable
 * getting a functional front-controller in place ASAP and support approaches
 * less reliant on global state, minimizing the number of requests flowing
 * through this path.
 */
readonly class FallbackRouter
{
    // This is a limited subset of assets to start. It only exists to support
    // `php -S` and shouldn't be relevant with a traditional webserver. The
    // list may still be expanded to ease development.
    private const STATIC_ASSET_EXTENSIONS = [
        // Images
        'ico',
        'png',
        'svg',
        // Styles
        'css',
        // Scripts
        'js',
        // Fonts
        'woff2',
        'ttf',
    ];

    /**
     * @param string $installRoot The absolute path to the root of the
     * installation (e.g. where composer.json and .git exist)
     */
    public function __construct(
        private string $installRoot,
        private LoggerInterface $logger,
    ) {
        // Sidenote: $_SERVER['DOCUMENT_ROOT'] seems to be pretty reliably the
        // same as intended $installRoot, but better to avoid relying on it.
    }

    /**
     * Determines the file that would have been directly requested, and
     * modifies superglobals in such a way that requests relying on that path
     * won't know the difference.
     *
     * Returns the absolute path to the legacy file to include, or null if it's
     * a static asset that should be handled by the webserver.
     *
     * @throws NotFoundHttpException if the path cannot be resolved or is blocked
     */
    public function performLegacyRouting(ServerRequestInterface $request): ?string
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        $this->logger->debug("Routing $path");

        // Rewrite rules: prefix → handler
        $handler = $this->getRewriteHandler($path);
        if ($handler !== null) {
            $file = realpath($this->installRoot . $handler);
            if ($file === false) {
                throw new OutOfRangeException('Rewrote to a non-existent file');
            }
            $this->logger->debug('Resolved to rewriter {file}', ['file' => $file]);
            $this->prepareRuntime($file);
            return $file;
        }

        // Literal path resolution
        $file = realpath($this->installRoot . $path);
        if ($file === false) {
            throw new NotFoundHttpException();
        }

        if (is_dir($file)) {
            $file = $this->handleDirectory($uri, $file);
        } elseif (!is_file($file)) {
            throw new NotFoundHttpException();
        }

        if (!$this->isPathAllowed($file)) {
            // This sends a 404 instead of a 403 to avoid disclosing what files
            // exist. It's of marginal value for source code since this is open
            // source software, but it's good for documents and such.
            throw new NotFoundHttpException();
        }

        if (self::isAllowedStaticAsset($file)) {
            return null;
        }

        $this->prepareRuntime($file);
        return $file;
    }

    /**
     * Mimic historic `.htaccess` front-controller-likes by mapping requests to
     * their intended entrypoint file.
     *
     * Returns null if no internal rewrite is required.
     */
    private function getRewriteHandler(string $path): ?string
    {
        // Order matters: specific prefixes before general ones
        return match (true) {
            str_starts_with($path, '/apis') => '/apis/dispatch.php',
            str_starts_with($path, '/oauth2') => '/oauth2/authorize.php',
            str_starts_with($path, '/meta/health') => '/meta/health/index.php',
            str_starts_with($path, '/portal/patient/fwk/libs') => null,
            str_starts_with($path, '/portal/patient') => '/portal/patient/index.php',
            str_starts_with($path, '/interface/modules/zend_modules/public') => '/interface/modules/zend_modules/public/index.php',
            default => null,
        };
    }

    /**
     * @throws HttpException 301 redirect if missing trailing slash
     * @throws NotFoundHttpException if no index.php
     */
    private function handleDirectory(UriInterface $uri, string $dir): string
    {
        $index = $dir . '/index.php';
        if (!file_exists($index)) {
            throw new NotFoundHttpException();
        }

        // If the request included the trailing slash, internally rewrite to
        // the index.php file.
        if (str_ends_with($uri->getPath(), '/')) {
            return $index;
        }

        // Redirect to add trailing slash. This ensures that forms (etc)
        // relying on relative paths will resolve to the correct place. Mimics
        // the default Apache DirectorySlash=On behavior.
        $redirectUri = $uri->withPath($uri->getPath() . '/');
        throw new HttpException(301, '', null, ['Location' => (string) $redirectUri]);
    }

    /**
     * Overrides various runtime values (including $_SERVER) in such a way that
     * it looks like the request went directly to the target file.
     */
    private function prepareRuntime(string $targetFile): void
    {
        // Directory change helps ensure relative paths (typically includes) work.
        $fileDirectory = pathinfo($targetFile, PATHINFO_DIRNAME);
        chdir($fileDirectory);

        // @phpstan-ignore openemr.forbiddenRequestGlobals
        $_SERVER['SCRIPT_FILENAME'] = $targetFile;

        // @phpstan-ignore openemr.forbiddenRequestGlobals, openemr.forbiddenRequestGlobals
        $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = substr($targetFile, strlen($this->installRoot));
    }

    /**
     * Test endpoint for E2E routing validation (no DB dependency).
     * Returns 418 with JSON identifying which entry point was reached.
     * Does nothing if the request URI doesn't end with /_routing_test.
     */
    public static function handleRoutingTestIfRequested(string $requestUri, string $entryPoint): void
    {
        if (!str_ends_with($requestUri, '/_routing_test')) {
            return;
        }
        http_response_code(418);
        header('Content-Type: application/json');
        echo json_encode(['routed' => $entryPoint]);
        exit;
    }

    /**
     * Hook for blocking or permitting paths
     *
     * @param string $path The absolute path to the file that may be `include`d
     */
    private function isPathAllowed(string $path): bool
    {
        // Block anything outside of the install root
        if (!str_starts_with(haystack: $path, needle: $this->installRoot)) {
            $this->logger->notice("Request outside of docroot, deny");
            return false;
        }
        // This is just to simplify the matching procedure
        $rootRelative = substr($path, strlen($this->installRoot));

        return match (true) {
            // All dotfiles and directories, including git
            str_starts_with($rootRelative, '/.') => false,
            // Obvious stuff that users shouldn't directly request (some
            // shouldn't even be on the server)
            str_starts_with($rootRelative, '/ci') => false,
            str_starts_with($rootRelative, '/config') => false,
            str_starts_with($rootRelative, '/db') => false,
            str_starts_with($rootRelative, '/docker') => false,
            str_starts_with($rootRelative, '/sql') => false,
            str_starts_with($rootRelative, '/src') => false,
            str_starts_with($rootRelative, '/tests') => false,
            str_starts_with($rootRelative, '/vendor') => false,
            // Package manager files (block anywhere)
            str_ends_with($rootRelative, '/composer.json') => false,
            str_ends_with($rootRelative, '/composer.lock') => false,
            str_ends_with($rootRelative, '/package.json') => false,
            str_ends_with($rootRelative, '/package-lock.json') => false,
            // Other non-executable content
            str_ends_with($rootRelative, '.inc') => false,
            str_ends_with($rootRelative, '.inc.php') => false,
            str_ends_with($rootRelative, '.tpl.php') => false,
            str_ends_with($rootRelative, '.twig') => false,
            // Covers most DB configs in most locations
            str_ends_with($rootRelative, 'sqlconf.php') => false,
            str_contains($rootRelative, '/templates') => false,
            // Historic `.htaccess` rules
            str_starts_with($rootRelative, '/bin') => false,
            str_starts_with($rootRelative, '/portal/patient/fwk/libs') => false,
            preg_match('#^/sites/[^/]+/documents/#', $rootRelative) === 1 => false,

            default => true, // Future: default deny instead?
        };
    }

    private static function isAllowedStaticAsset(string $path): bool
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        return in_array($ext, self::STATIC_ASSET_EXTENSIONS, strict: true);
    }
}
