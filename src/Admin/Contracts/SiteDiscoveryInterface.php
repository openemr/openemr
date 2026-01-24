<?php

/**
 * Site Discovery Interface
 *
 * Contract for site discovery services.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Contracts;

interface SiteDiscoveryInterface
{
    /**
     * Discover all configured sites
     *
     * @return array<int, string> Array of site directory names
     */
    public function discoverSites(): array;

    /**
     * Validate site name format
     */
    public function isValidSiteName(string $siteName): bool;

    /**
     * Get the path to a site's configuration file
     */
    public function getSiteConfigPath(string $siteName): string;
}
