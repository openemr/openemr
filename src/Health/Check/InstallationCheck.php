<?php

/**
 * InstallationCheck - Verifies OpenEMR installation status
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Health\Check;

use OpenEMR\BC\SiteInstallState;
use OpenEMR\Health\HealthCheckInterface;
use OpenEMR\Health\HealthCheckResult;

class InstallationCheck implements HealthCheckInterface
{
    public const NAME = 'installed';

    public function __construct(private readonly SiteInstallState $installState)
    {
    }

    public function getName(): string
    {
        return static::NAME;
    }

    public function check(): HealthCheckResult
    {
        if (!$this->installState->isInstalled) {
            return new HealthCheckResult(
                $this->getName(),
                false,
                'OpenEMR setup required'
            );
        }

        return new HealthCheckResult($this->getName(), true);
    }
}
