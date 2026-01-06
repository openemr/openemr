<?php

/**
 * InstallationCheck - Verifies OpenEMR installation status
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com/
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Health\Check;

use OpenEMR\Health\HealthCheckInterface;
use OpenEMR\Health\HealthCheckResult;

class InstallationCheck implements HealthCheckInterface
{
    public const NAME = 'installed';

    public function getName(): string
    {
        return static::NAME;
    }

    public function check(): HealthCheckResult
    {
        try {
            // The $config variable is set in sqlconf.php
            // $config = 0 means not installed, $config = 1 means installed
            global $config;

            if (!isset($config)) {
                return new HealthCheckResult(
                    $this->getName(),
                    false,
                    'Configuration not loaded'
                );
            }

            if ($config !== 1) {
                return new HealthCheckResult(
                    $this->getName(),
                    false,
                    'OpenEMR setup required'
                );
            }

            return new HealthCheckResult($this->getName(), true);
        } catch (\Throwable $e) {
            return new HealthCheckResult(
                $this->getName(),
                false,
                $e->getMessage()
            );
        }
    }
}
