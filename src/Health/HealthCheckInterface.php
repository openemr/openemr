<?php

/**
 * HealthCheckInterface - Contract for health checks
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com/
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Health;

interface HealthCheckInterface
{
    /**
     * Get the name of this health check
     */
    public function getName(): string;

    /**
     * Perform the health check
     */
    public function check(): HealthCheckResult;
}
