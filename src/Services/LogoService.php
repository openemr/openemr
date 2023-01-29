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
    public function getLogo(string $type): string
    {
        $siteDir = "{$GLOBALS['OE_SITE_DIR']}/images/logos/{$type}/";
        $paths[] = "{$GLOBALS['images_static_absolute']}/logos/{$type}/";

        if ($this->fs->exists($siteDir)) {
            // Only look in sites if the sites structure exists, ensures upgrades continue to work
            array_unshift($paths, $siteDir);
        }

        $logo = $this->findLogo($paths);

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
        return str_replace(array_keys($paths), array_values($paths), $path);
    }

    private function findLogo(array $directory, string $filename = 'logo.*'): string|null
    {
        $this->finder->files()->in($directory)->name($filename);

        if ($this->finder->hasResults()) {
            // There is at least 1 file in the sites directory for the given logo
            foreach ($this->finder as $f) {
                $return = $f->getRealPath();
            }
        } else {
            $return = null;
        }

        return $return;
    }
}
