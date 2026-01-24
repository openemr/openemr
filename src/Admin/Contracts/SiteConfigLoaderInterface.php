<?php

/**
 * Site Config Loader Interface
 *
 * Contract for site configuration loading services.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Contracts;

use OpenEMR\Admin\ValueObjects\DatabaseCredentials;

interface SiteConfigLoaderInterface
{
    /**
     * Load database credentials from configuration file
     *
     * @throws \OpenEMR\Admin\Exceptions\SiteConfigException
     */
    public function loadCredentials(string $configPath): DatabaseCredentials;

    /**
     * Check if site needs initial setup
     */
    public function siteNeedsSetup(string $configPath): bool;
}
