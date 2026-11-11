<?php

/**
 * Store and display the Eye Form practice logo within the active site.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 Ray Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag\Assets;

use GdImage;

/** Return the shared writable practice-logo location for this site. */
function eye_mag_logo_site_path(string $siteDirectory): string
{
    return rtrim($siteDirectory, '/') . '/documents/practice_assets/practice_logo.gif';
}

/** Prefer the shared site logo; keep using an existing OpenEMR logo as a fallback. */
function eye_mag_logo_path(string $siteDirectory): string
{
    $sitePath = eye_mag_logo_site_path($siteDirectory);
    return is_file($sitePath) && is_readable($sitePath) && !is_link($sitePath)
        ? $sitePath : rtrim($siteDirectory, '/') . '/images/practice_logo.gif';
}

/** Build a browser image source without exposing a site's private documents directory. */
function eye_mag_logo_preview(string $siteDirectory, string $webRoot): string
{
    $path = eye_mag_logo_path($siteDirectory);
    if (!is_file($path) || !is_readable($path)) {
        return '';
    }
    if ($path === eye_mag_logo_site_path($siteDirectory)) {
        $bytes = file_get_contents($path);
        return is_string($bytes) && $bytes !== '' ? 'data:image/gif;base64,' . base64_encode($bytes) : '';
    }
    return rtrim($webRoot, '/') . '/sites/' . rawurlencode(basename(rtrim($siteDirectory, '/')))
        . '/images/practice_logo.gif?t=' . (int) filemtime($path);
}

/** Refuse a redirected destination immediately before making the new logo visible. */
function eye_mag_logo_replace(string $temporary, string $path): bool
{
    clearstatcache(true, $path);
    return !is_link($path) && @rename($temporary, $path);
}

/** Atomically replace only this site's shared practice logo, preserving an existing logo on failure. */
function eye_mag_save_practice_logo(GdImage $image, string $siteDirectory): bool
{
    $path = eye_mag_logo_site_path($siteDirectory);
    $directory = dirname($path);
    $documents = rtrim($siteDirectory, '/') . '/documents';
    if (!is_dir($documents) || (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory))
        || is_link($directory) || is_link($path)) {
        return false;
    }
    $temporary = @tempnam($directory, '.practice-logo-');
    if ($temporary === false || realpath(dirname($temporary)) !== realpath($directory)) {
        if (is_string($temporary)) {
            @unlink($temporary);
        }
        return false;
    }
    try {
        if (!@imagegif($image, $temporary) || !@chmod($temporary, 0644) || !eye_mag_logo_replace($temporary, $path)) {
            return false;
        }
        clearstatcache(true, $path);
        return true;
    } finally {
        if (is_file($temporary)) {
            @unlink($temporary);
        }
    }
}
