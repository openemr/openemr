<?php

/**
 * Site Discovery Service
 *
 * Handles filesystem scanning and site discovery.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Services;

use OpenEMR\Admin\Contracts\SiteDiscoveryInterface;
use OpenEMR\Admin\Exceptions\InvalidSiteNameException;

class SiteDiscoveryService implements SiteDiscoveryInterface
{
    private const VALID_SITE_NAME_PATTERN = '/^[a-zA-Z0-9._-]+$/';

    public function __construct(private readonly string $sitesBaseDir)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function discoverSites(): array
    {
        $sites = [];
        $handle = @opendir($this->sitesBaseDir);

        if (!$handle) {
            error_log("Cannot read sites directory: {$this->sitesBaseDir}");
            return $sites;
        }

        while (false !== ($filename = readdir($handle))) {
            // Skip hidden files and CVS
            if (str_starts_with($filename, '.') || $filename === 'CVS') {
                continue;
            }

            // Validate site name format
            if (!$this->isValidSiteName($filename)) {
                error_log("Invalid site name format: $filename");
                continue;
            }

            $siteDir = "{$this->sitesBaseDir}/{$filename}";

            // Verify directory exists
            if (!is_dir($siteDir)) {
                continue;
            }

            // Verify sqlconf.php exists
            if (!is_file("{$siteDir}/sqlconf.php")) {
                continue;
            }

            $sites[] = $filename;
        }

        closedir($handle);
        sort($sites);

        return $sites;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidSiteName(string $siteName): bool
    {
        return !empty($siteName) && preg_match(self::VALID_SITE_NAME_PATTERN, $siteName) === 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getSiteConfigPath(string $siteName): string
    {
        if (!$this->isValidSiteName($siteName)) {
            throw new InvalidSiteNameException($siteName);
        }

        return "{$this->sitesBaseDir}/{$siteName}/sqlconf.php";
    }
}
