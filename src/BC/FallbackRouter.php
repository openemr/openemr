<?php

declare(strict_types=1);

namespace OpenEMR\BC;

readonly class FallbackRouter
{
    /**
     * @param string $installRoot The absolute path to the root of the
     * installation (e.g. where composer.json and .git exist)
     */
    public function __construct(
        private string $installRoot,
    ) {
    }

    /**
     * Determines the file that would have been directly requested, and
     * modifies superglobals in such a way that requests relying on that path
     * won't know the difference.
     *
     * @param string $requestUri The value of $_SERVER['REQUEST_URI']
     *
     * @return string The absolute path to the legacy file to include
     */
    public function performLegacyRouting(string $requestUri): string
    {
        $this->log("PERFORMING LEGACY ROUTING");
        if ($requestUri === '/') {
            // Special-case the "index" requests
            $requestUri = '/index.php';
        }
        $this->log("REQUEST_URI=$requestUri");

        // PHP-equivalent to `.htaccess` mod_rewrite rules
        $path = match (true) {
            str_starts_with($requestUri, '/apis') => '/apis/dispatch.php',
            default => parse_url($requestUri, PHP_URL_PATH),
        };

        $this->log("path=$path");
        // Normalize the included file to the webroot
        $path = realpath(sprintf('%s%s', $this->installRoot, $path));

        $fileDirectory = pathinfo($path, PATHINFO_DIRNAME);
        chdir($fileDirectory);

        // This seems fairly reliably to be $_SERVER['DOCUMENT_ROOT']
        if ($path === false || !str_starts_with(needle: $this->installRoot, haystack: $path)) {
            header('HTTP/1.1 404 Not Found');
            echo 'Invalid path';
            exit(1);
        }

        $this->overrideGlobals($path);
        $this->log("include=$path");
        return $path;
    }

    /**
     * Overrides values in $_SERVER in such a way that it looks like the
     * request went directly to the target file
     */
    private function overrideGlobals(string $targetFile): void
    {

        $_SERVER['SCRIPT_FILENAME'] = $targetFile;

        $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = substr($targetFile, strlen($this->installRoot));
        // SCRIPT_NAME = PHP_SELF = docroot-relative
        // DOCUMENT_ROOT seems ok
    }

    private function log(string $message): void
    {
        // Future scope: Have a PSR-3 logger injected and make calls
        // (debug-level?) to it instead.
        error_log($message);
    }
}
