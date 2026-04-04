<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\BC;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

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
 *
 * .htaccess files:
 *
 * Deny rules:
 * - [x] ./bin/.htaccess
 * - [x] ./portal/patient/fwk/libs/.htaccess
 * - [x] ./sites/default/documents/.htaccess
 *
 * Rewrites:
 * - [x] ./apis/.htaccess
 * - [x] ./interface/modules/custom_modules/oe-module-faxsms/.htaccess (no path rewrite needed; only adds query param)
 * - [x] ./interface/modules/zend_modules/public/.htaccess
 * - [x] ./meta/health/.htaccess
 * - [x] ./oauth2/.htaccess
 * - [x] ./portal/patient/.htaccess
 */
readonly class FallbackRouter
{
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
     * @return ?string The absolute path to the legacy file to include, or null
     * if not routable.
     */
    public function performLegacyRouting(ServerRequestInterface $request): ?string
    {
        $requestUri = $request->getUri()->getPath();
        if (str_ends_with($requestUri, '/')) {
            // Special-case the "index" requests
            $requestUri .= 'index.php';
        }

        // PHP-equivalent to `.htaccess` mod_rewrite rules
        // Order matters: more specific paths must come before general prefixes
        $path = match (true) {
            str_starts_with($requestUri, '/apis') => '/apis/dispatch.php',
            str_starts_with($requestUri, '/oauth2') => '/oauth2/authorize.php',
            str_starts_with($requestUri, '/meta/health') => '/meta/health/index.php',
            // fwk/libs has deny-all in its .htaccess, so don't rewrite (let isPathAllowed block it)
            str_starts_with($requestUri, '/portal/patient/fwk/libs') => parse_url($requestUri, PHP_URL_PATH),
            str_starts_with($requestUri, '/portal/patient') => '/portal/patient/index.php',
            str_starts_with($requestUri, '/interface/modules/zend_modules/public') => '/interface/modules/zend_modules/public/index.php',
            default => parse_url($requestUri, PHP_URL_PATH),
        };

        // Normalize the included file to the webroot
        $path = realpath(sprintf('%s%s', $this->installRoot, $path));
        if ($path === false) {
            // Future: 400/404
            return null;
        }
        if (!is_file($path)) {
            // Might have been a directory
            // Future: 404
            return null;
        }

        if (!$this->isPathAllowed($path)) {
            // Future: 403
            return null;
        }

        $this->prepareRuntime($path);
        return $path;
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
        if (!str_starts_with(needle: $this->installRoot, haystack: $path)) {
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
            str_starts_with($rootRelative, '/config') => false,
            str_starts_with($rootRelative, '/db') => false,
            str_starts_with($rootRelative, '/sql') => false,
            str_starts_with($rootRelative, '/src') => false,
            str_starts_with($rootRelative, '/tests') => false,
            str_starts_with($rootRelative, '/vendor') => false,
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
}
