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

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Core\ModulesApplication;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Services\LogoFilterEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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

    private readonly EventDispatcherInterface $dispatcher;

    public function __construct(?EventDispatcherInterface $dispatcher = null)
    {
        // Ensure a finder object exists
        $this->resetFinder();
        $this->fs = new Filesystem();

        // cleanest way to refactor for now, is to fallback to global dispatcher
        // don't like it though
        $this->dispatcher = $dispatcher ?? OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher();
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
        $siteDir = OEGlobalsBag::getInstance()->get('OE_SITE_DIR') . "/images/logos/" . $type . "/";
        $publicDir = OEGlobalsBag::getInstance()->getKernel()->getImagesAbsolute() . "/logos/" . $type . "/";
        $paths = [];

        if ($this->fs->exists($publicDir)) {
            $paths[] = $publicDir;
        }

        if ($this->fs->exists($siteDir)) {
            $paths[] = $siteDir;
        }

        try {
            $logo = $this->findLogo($paths, $filename);
        } catch (\Throwable $e) {
            ServiceContainer::getLogger()->error('LogoService::getLogo failed', ['exception' => $e]);
            $logo = "";
        }

        // This is critical, the finder must be completely re-instantiated to ensure the proper directories are searched next time.
        $this->resetFinder();

        $webPath = $this->convertToWebPath($logo);
        $logoFilterEvent = new LogoFilterEvent($type, $logo, $webPath);
        $filteredEvent = $this->dispatcher->dispatch($logoFilterEvent, LogoFilterEvent::EVENT_NAME);
        $updatedWebPath = $filteredEvent->getWebPath();
        // make sure the web path is safe for the logo
        if ($updatedWebPath != $webPath) {
            $safePaths = ModulesApplication::filterSafeLocalModuleFiles([$updatedWebPath]);
            return $safePaths[0] ?? '';
        }
        return $webPath;
    }

    /**
     * Convert a path between absolute and web-friendly paths for sites and images paths
     *
     * @param string $path
     * @return string
     */
    private function convertToWebPath(string $path): string
    {
        $kernel = OEGlobalsBag::getInstance()->getKernel();
        $paths = [
            OEGlobalsBag::getInstance()->get('OE_SITE_DIR') => OEGlobalsBag::getInstance()->get('OE_SITE_WEBROOT'),
            $kernel->getImagesAbsolute() => $kernel->getImagesRelative(),
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
     * @param list<string> $directory Array of directories to search
     * @param string  $filename  File to look for
     * @param bool $timestamp Will return with a query string of the last modified time
     * @return string String of real path or empty string if no file found
     */
    private function findLogo(array $directory, string $filename = 'logo.*', $timestamp = true): string
    {
        // Filter to only directories that actually exist and are
        // readable. On NFS-backed sites volumes (typical Kubernetes
        // deployments), an opendir() against a missing or
        // transiently-unavailable directory returns EREMOTEIO rather
        // than ENOENT, and RecursiveDirectoryIterator surfaces that
        // as an UnexpectedValueException from inside Finder's lazy
        // iterator — downstream the exception is harder to catch and
        // can leak into test output under PHPUnit's strict-output
        // mode. Pre-filtering with is_dir() removes the error path
        // at source; is_dir() returns false cleanly on missing or
        // unreadable without raising a warning.
        $existing = [];
        foreach ($directory as $d) {
            if (is_dir($d)) {
                $existing[] = $d;
            }
        }

        if (empty($existing)) {
            return "";
        }
        $directory = $existing;

        $this->finder->files()->in($directory)->name($filename);

        $return = "";
        // Defensive catch around Finder's lazy iteration: is_dir()
        // above can still TOCTOU-race if a directory is removed
        // between the check and the walk (unlikely but possible on
        // NFS). Degrade gracefully to "no logo" rather than let the
        // exception propagate.
        try {
            if ($this->finder->hasResults()) {
                // There is at least 1 file in the sites directory for the given logo
                foreach ($this->finder as $f) {
                    $return = $f->getRealPath();
                    $return = ($timestamp) ? $return . "?t=" . $f->getMTime() : $return;
                }
            }
        } catch (\UnexpectedValueException $e) {
            ServiceContainer::getLogger()->error('LogoService::findLogo iteration failed', ['exception' => $e]);
            return "";
        }

        return $return;
    }
}
