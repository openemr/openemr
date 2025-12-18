<?php

declare(strict_types=1);

namespace OpenEMR\BC;

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
    /**
     * @param string $installRoot The absolute path to the root of the
     * installation (e.g. where composer.json and .git exist)
     */
    public function __construct(
        private string $installRoot,
    ) {
        // Sidenote: $_SERVER['DOCUMENT_ROOT'] seems to be pretty reliably the
        // same as intended $installRoot, but better to avoid relying on it.
    }

    /**
     * Determines the file that would have been directly requested, and
     * modifies superglobals in such a way that requests relying on that path
     * won't know the difference.
     *
     * @param string $requestUri The value of $_SERVER['REQUEST_URI']
     *
     * @return ?string The absolute path to the legacy file to include, or null
     * if not routable.
     */
    public function performLegacyRouting(string $requestUri): ?string
    {
        $this->debug("PERFORMING LEGACY ROUTING");
        if ($requestUri === '/') {
            // Special-case the "index" requests
            $requestUri = '/index.php';
        }
        $this->debug("REQUEST_URI=$requestUri");

        // PHP-equivalent to `.htaccess` mod_rewrite rules
        $path = match (true) {
            // Note: not reachable until `/apis/.htaccess` is removed/bypassed
            str_starts_with($requestUri, '/apis') => '/apis/dispatch.php',
            default => parse_url($requestUri, PHP_URL_PATH),
        };

        $this->debug("path=$path");

        // Normalize the included file to the webroot
        $path = realpath(sprintf('%s%s', $this->installRoot, $path));
        if ($path === false) {
            $this->debug('Not resolvable');
            return null;
        }
        if (!is_file($path)) {
            // Might have been a directory
            $this->debug('No file');
            return null;
        }

        if (!$this->isPathAllowed($path)) {
            $this->debug("BLOCKED $path");
            return null;
        }


        $this->prepareRuntime($path);
        $this->debug("include=$path");
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

    private function debug(string $message): void
    {
        // Future scope: Have a PSR-3 logger injected and make calls
        // (debug-level?) to it instead.
        error_log($message);
    }

    /**
     * Hook for blocking or permitting paths
     *
     * @param string $path The absolute path to the file that may be `include`d
     */
    private function isPathAllowed(string $path): bool
    {
        $this->debug("IPA=$path");
        // Block anything outside of the install root
        if (!str_starts_with(needle: $this->installRoot, haystack: $path)) {
            $this->debug("Outside of docroot, deny");
            return false;
        }
        // This is just to simplify the matching procedure
        $rootRelative = substr($path, strlen($this->installRoot));
        $this->debug("IPA/R=$rootRelative");

        // TODO:
        // - Any equivalent `.htaccess` deny rules
        // - configs
        return match (true) {
            // All dotfiles and directories, including git
            str_starts_with($rootRelative, '/.') => false,
            // Obvious stuff that users shouldn't directly request
            str_starts_with($rootRelative, '/src') => false,
            str_starts_with($rootRelative, '/tests') => false,
            str_starts_with($rootRelative, '/vendor') => false,
            // Other non-executable content
            str_ends_with($rootRelative, '.inc') => false,
            str_ends_with($rootRelative, '.inc.php') => false,
            // Covers most DB configs in most locations
            str_ends_with($rootRelative, 'sqlconf.php') => false,
            default => true, // Future: default deny instead?
        };
    }
}
