<?php

/**
 * Keep provider signature changes within the active site while retaining legacy images.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 Ray Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag;

use OpenEMR\Core\OEGlobalsBag;

final readonly class ProviderSignature
{
    private const SITE_DIRECTORY = '/images/eye_mag/signatures/';

    public function __construct(
        private string $siteDir,
        private string $siteWebRoot,
        private string $projectDir,
        private string $webRoot,
    ) {
    }

    public static function fromGlobals(OEGlobalsBag $globals): self
    {
        return new self(
            $globals->getString('OE_SITE_DIR'),
            $globals->getString('OE_SITE_WEBROOT'),
            $globals->getProjectDir(),
            $globals->getWebRoot(),
        );
    }

    /** New signatures always belong to the active site. */
    public function sitePath(int $providerId): string
    {
        return $this->siteDir . self::SITE_DIRECTORY . 'sign_' . $providerId . '.jpg';
    }

    public function path(int $providerId): string
    {
        $sitePath = $this->sitePath($providerId);
        return is_file($sitePath) && !is_link($sitePath)
            ? $sitePath
            : $this->projectDir . '/interface/forms/eye_mag/images/sign_' . $providerId . '.jpg';
    }

    public function siteUrl(int $providerId): string
    {
        return $this->siteWebRoot . self::SITE_DIRECTORY . 'sign_' . $providerId . '.jpg';
    }

    public function url(int $providerId): string
    {
        return $this->path($providerId) === $this->sitePath($providerId)
            ? $this->siteUrl($providerId)
            : $this->webRoot . '/interface/forms/eye_mag/images/sign_' . $providerId . '.jpg';
    }
}
