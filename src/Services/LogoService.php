<?php

/**
 * Login Service class.
 *
 * Business logic for the login page
 *
 * @package     OpenEMR
 * @subpackage  Login
 * @author      Robert Down <robertdown@live.com>
 * @copyright   Copyright (c) 2023 Robert Down
 * @copyright   Providence Healthtech
 * @license     https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class LogoService
{
    /**
     * Finder class
     *
     * @var Finder
     */
    private $finder;

    /**
     * Filesystem class
     *
     * @var Filesystem
     */
    private $fs;

    public function __construct()
    {
        // Ensure a finder object exists
        $this->resetFinder();
        $this->fs = new Filesystem();
    }

    private function resetFinder()
    {
        $this->finder = new Finder();
    }

    public function reset()
    {
        $this->resetFinder();
    }

    /**
     * Get a logo, if one exists. Ignores any rendering options, just returns a filepath
     *
     * $type matches the filepath of the logo, (i.e.) core/login/primary or
     * core/login/secondary. The fallback paths are automatically matched here,
     * concatenating the $type argument with the images_static_absolute path for
     * all logos.
     *
     * @param string $type
     * @return string
     */
    public function getLogo(string $type, string $filename = "logo.*"): string
    {
        $siteDir = "{$GLOBALS['OE_SITE_DIR']}/images/logos/{$type}/";
        $publicDir = "{$GLOBALS['images_static_absolute']}/logos/{$type}/";
        $paths = [];

        if ($this->fs->exists($publicDir)) {
            $paths[] = $publicDir;
        }

        if ($this->fs->exists($siteDir)) {
            $paths[] = $siteDir;
        }

        try {
            $logo = $this->findLogo($paths, $filename);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $logo = "";
        }

        // This is critical, the finder must be completely reinstantiated to ensure the proper directories are searched next time.
        $this->resetFinder();

        return $this->convertToWebPath($logo);
    }

    /**
     * Convert a path between absolute and web-friendly paths for sites and images paths
     *
     * @param string $path
     * @return string
     */
    private function convertToWebPath(string $path): string
    {
        $paths = [
            $GLOBALS['OE_SITE_DIR'] => $GLOBALS['OE_SITE_WEBROOT'],
            $GLOBALS['images_static_absolute'] => $GLOBALS['images_static_relative'],
        ];
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $path = str_replace('\\', '/', $path);
        }
        return str_replace(array_keys($paths), array_values($paths), $path);
    }

    /**
     * Search in the given directories for a filename
     *
     * By default, will search in the directory array for any file named "logo" (extension agnostic). If found, only
     * the last file found will be returned. By default, will append a query string for time modified to cache bust.
     *
     * @param array $directory Array of directories to search
     * @param string $filename File to look for
     * @param boolean $timestamp Will return with a query string of the last modified time
     * @return string|null String of real path or null if no file found
     */
    private function findLogo(array $directory, string $filename = 'logo.*', $timestamp = true): string
    {
        $this->finder->files()->in($directory)->name($filename);

        if ($this->finder->hasResults()) {
            // There is at least 1 file in the sites directory for the given logo
            foreach ($this->finder as $f) {
                $return = $f->getRealPath();
                $return = ($timestamp) ? $return . "?t=" . $f->getMTime() : $return;
            }
        } else {
            $return = "";
        }

        return $return;
    }
}
