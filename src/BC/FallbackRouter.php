<?php

declare(strict_types=1);

namespace OpenEMR\BC;

readonly class FallbackRouter
{
    public function __construct(
        private string $documentRoot,
    ) {
    }

    /**
     * @param array<string, string|int> $serverGlobals a copy of $_SERVER
     *
     * @return string The absolute path to the legacy file to include
     */
    public function performLegacyRouting(array $serverGlobals): string
    {
        $this->log("PERFORMING FRONT CONTROLLER ROUTING");
        // debugGlobals();
        // ksort($serverGlobals);
        $requestUri = $serverGlobals['REQUEST_URI'];
        if ($requestUri === '/') {
            // Special-case the document root
            $requestUri = '/index.php';
        }
        error_log("REQUEST_URI=$requestUri");

        // PHP-equivalent to `.htaccess` mod_rewrite rules
        $path = match (true) {
            str_starts_with($requestUri, '/apis') => '/apis/dispatch.php',
            default => parse_url($requestUri, PHP_URL_PATH),
        };

        error_log("path=$path");
        // Normalize the included file to the webroot
        // $path = realpath(__DIR__ . '/../' . $path);
        $path = realpath(sprintf('%s%s', $this->documentRoot, $path));

        $fileDirectory = pathinfo($path, PATHINFO_DIRNAME);
        chdir($fileDirectory);

        // This seems fairly reliably to be $_SERVER['DOCUMENT_ROOT']
        if ($path === false || !str_starts_with(needle: $this->documentRoot, haystack: $path)) {
            header('HTTP/1.1 404 Not Found');
            echo 'Invalid path';
            exit(1);
        }

        $this->overrideGlobals($path);
        error_log("include=$path");
        // unscopedRequire($path);
        // exit(0);
        return $path;
    }

    /**
     * Overrides values in $_SERVER in such a way that it looks like the
     * request went directly to the target file
     */
    private function overrideGlobals(string $targetFile): void
    {

        $_SERVER['SCRIPT_FILENAME'] = $targetFile;

        $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = substr($targetFile, strlen($this->documentRoot));
        // SCRIPT_NAME = PHP_SELF = docroot-relative
        // DOCUMENT_ROOT seems ok
    }

    private function log(string $message): void
    {
        // Future scope: Have a PSR-3 logger injected and make calls
        // (debug-level?) to it instead.
        error_log($message);
    }

    private function debugGlobals(): never
    {
        header('Content-type: text/plain');
        echo "Hello from front controller";

        ksort($_SERVER);
        print_r($_SERVER);
        print_r($_GET);
        print_r($_POST);

        exit;
    }
}
