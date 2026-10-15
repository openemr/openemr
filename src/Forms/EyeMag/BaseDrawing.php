<?php

/**
 * The base drawing an eye exam zone's sketch starts from. A site can supply
 * its own in sites/<site>/images/eye_mag/ (same file name as the form's, e.g.
 * OU_RETINA_BASE.jpg); otherwise the form's stock drawing is used. This lets
 * one practice use its own anatomy drawings without changing them for every
 * site on the install.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag;

use OpenEMR\Core\OEGlobalsBag;

final readonly class BaseDrawing
{
    /** Where a site keeps its own drawings, under its site directory. */
    public const SITE_DIRECTORY = '/images/eye_mag/';

    /**
     * @param string $siteDir the site's directory on disk (OE_SITE_DIR)
     * @param string $siteWebRoot the site's directory as a URL path (OE_SITE_WEBROOT)
     * @param string $webRoot the install's URL path (webroot)
     */
    public function __construct(
        private string $siteDir,
        private string $siteWebRoot,
        private string $webRoot,
    ) {
    }

    public static function fromGlobals(OEGlobalsBag $globals): self
    {
        return new self(
            $globals->getString('OE_SITE_DIR'),
            $globals->getString('OE_SITE_WEBROOT'),
            $globals->getWebRoot(),
        );
    }

    /**
     * URL of the base drawing for a zone, e.g. side "OU", zone "RETINA".
     */
    public function url(string $side, string $zone): string
    {
        $file = $side . '_' . $zone . '_BASE.jpg';
        $stock = $this->webRoot . '/interface/forms/eye_mag/images/' . $file;
        // Only plain names reach the file system.
        if (preg_match('/^[A-Z]+_[A-Z]+_BASE\.jpg$/', $file) !== 1) {
            return $stock;
        }
        if ($this->siteDir !== '' && is_file($this->siteDir . self::SITE_DIRECTORY . $file)) {
            return $this->siteWebRoot . self::SITE_DIRECTORY . $file;
        }
        return $stock;
    }
}
