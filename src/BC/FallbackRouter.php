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
        // Sidenote: $_SERVER['DOCUMENT_ROOT'] seems to be pretty reliable in
        // common installations, but the less relying on globals, the better.
    }

    /**
     * Determines the file that would have been directly requested, and
     * modifies superglobals in such a way that requests relying on that path
     * won't know the difference.
     *
     * @param string $requestUri The value of $_SERVER['REQUEST_URI']
     *
     * @return ?string The absolute path to the legacy file to include, or null
     * if not routable;
     */
    public function performLegacyRouting(string $requestUri): ?string
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
        if ($path === false) {
            $this->log('No file');
            return null;
        }

        if (!$this->isPathAllowed($path)) {
            $this->log("BLOCKED $path");
            return null;
        }


        $this->prepareRuntime($path);
        $this->log("include=$path");
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

        $_SERVER['SCRIPT_FILENAME'] = $targetFile;

        $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = substr($targetFile, strlen($this->installRoot));
    }

    private function log(string $message): void
    {
        // Future scope: Have a PSR-3 logger injected and make calls
        // (debug-level?) to it instead.
        error_log($message);
    }

    /**
     * Hook for blocking or permitting paths
     */
    private function isPathAllowed(string $path): bool
    {
        $this->log("IPA=$path");
        if (!str_starts_with(needle: $this->installRoot, haystack: $path)) {
            $this->log("Outside of docroot, deny");
            return false;
        }
        $rootRelative = substr($path, strlen($this->installRoot));
        $this->log("IPA/R=$rootRelative");

        // Copy through the equivalent of htaccess "deny all" rules
        // Block:
        // - config paths
        // - anything below the install root
        return match (true) {
            // All dotfiles and directories, including git
            str_starts_with($rootRelative, '/.') => false,
            // Obvious stuff that users shouldn't directly request
            str_starts_with($rootRelative, '/src') => false,
            str_starts_with($rootRelative, '/tests') => false,
            str_starts_with($rootRelative, '/vendor') => false,
            // Other non-executable content
            str_ends_with($rootRelative, '.inc.php') => false,
            default => true, // Future: default deny instead?
        };
    }
}
