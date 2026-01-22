<?php

/**
 * Site Info Service
 *
 * Orchestrates site discovery, configuration loading, and version reading
 * to build complete site information objects.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Services;

use OpenEMR\Admin\Contracts\DatabaseConnectorInterface;
use OpenEMR\Admin\Contracts\SiteConfigLoaderInterface;
use OpenEMR\Admin\Contracts\SiteDiscoveryInterface;
use OpenEMR\Admin\Contracts\SiteVersionReaderInterface;
use OpenEMR\Admin\Exceptions\DatabaseConnectionException;
use OpenEMR\Admin\Exceptions\DatabaseQueryException;
use OpenEMR\Admin\Exceptions\SiteConfigException;
use OpenEMR\Admin\ValueObjects\Site;

class SiteInfoService
{
    public function __construct(private readonly SiteDiscoveryInterface $discovery, private readonly SiteConfigLoaderInterface $configLoader, private readonly DatabaseConnectorInterface $connector, private readonly SiteVersionReaderInterface $versionReader)
    {
    }

    /**
     * Get information for a single site
     */
    public function getSiteInfo(string $siteName): Site
    {
        $configPath = $this->discovery->getSiteConfigPath($siteName);

        // Check if site needs setup
        if ($this->configLoader->siteNeedsSetup($configPath)) {
            return Site::needsSetup($siteName);
        }

        // Try to load credentials
        try {
            $credentials = $this->configLoader->loadCredentials($configPath);
        } catch (SiteConfigException $e) {
            error_log("Config error for site {$siteName}: " . $e->getMessage());
            return Site::withError($siteName, '', 'MySQL connect failed');
        }

        // Try to connect to database
        try {
            $connection = $this->connector->getConnection($credentials);
        } catch (DatabaseConnectionException $e) {
            error_log("Connection error for site {$siteName}: " . $e->getMessage());
            return Site::withError($siteName, $credentials->getDbase(), 'MySQL connect failed');
        }

        // Try to read site information from database
        try {
            $siteDisplayName = $this->versionReader->getSiteName($connection);
            $version = $this->versionReader->readVersion($connection);
            $expected = $this->versionReader->getExpectedVersions();

            // Determine upgrade status
            $upgradeStatus = $version->determineUpgradeStatus(
                $expected['database'],
                $expected['acl'],
                $expected['patch']
            );

            return Site::create(
                siteId: $siteName,
                dbName: $credentials->getDbase(),
                siteName: $siteDisplayName,
                version: $version->toString(),
                requiresUpgrade: $upgradeStatus['requiresUpgrade'],
                upgradeType: $upgradeStatus['upgradeType'],
                isCurrent: $upgradeStatus['isCurrent']
            );
        } catch (DatabaseQueryException $e) {
            error_log("Query error for site {$siteName}: " . $e->getMessage());
            return Site::withError($siteName, $credentials->getDbase(), 'Database query failed');
        }
    }

    /**
     * Get information for all discovered sites
     *
     * @return array<int, Site>
     */
    public function getAllSitesInfo(): array
    {
        $siteNames = $this->discovery->discoverSites();
        $sites = [];

        foreach ($siteNames as $siteName) {
            $sites[] = $this->getSiteInfo($siteName);
        }

        return $sites;
    }
}
