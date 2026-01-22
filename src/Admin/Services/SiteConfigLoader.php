<?php

/**
 * Site Config Loader Service
 *
 * Loads site configuration from sqlconf.php files.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Services;

use OpenEMR\Admin\Contracts\SiteConfigLoaderInterface;
use OpenEMR\Admin\Exceptions\SiteConfigException;
use OpenEMR\Admin\ValueObjects\DatabaseCredentials;

class SiteConfigLoader implements SiteConfigLoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function loadCredentials(string $configPath): DatabaseCredentials
    {
        if (!file_exists($configPath)) {
            throw new SiteConfigException("Configuration file not found", $configPath);
        }

        if (!is_readable($configPath)) {
            throw new SiteConfigException("Configuration file not readable", $configPath);
        }

        // Variables expected from sqlconf.php
        /** @var bool|null $config */
        $config = null;
        /** @var string|null $host */
        $host = null;
        /** @var string|null $login */
        $login = null;
        /** @var string|null $pass */
        $pass = null;
        /** @var string|null $dbase */
        $dbase = null;
        /** @var int $port */
        $port = 3306;

        // Include the configuration file
        include $configPath;

        // Check if config is set (indicates setup is complete)
        if (!$config) {
            throw new SiteConfigException("Site configuration not initialized", $configPath);
        }

        // Create and return credentials (validation happens in constructor)
        try {
            return new DatabaseCredentials($host, $login, $pass, $dbase, $port);
        } catch (SiteConfigException $e) {
            throw new SiteConfigException(
                "Invalid database credentials in configuration: " . $e->getMessage(),
                $configPath,
                0,
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function siteNeedsSetup(string $configPath): bool
    {
        if (!file_exists($configPath)) {
            return true;
        }

        /** @var bool|null $config */
        $config = null;

        // Suppress any warnings from include
        @include $configPath;

        return !$config;
    }
}
