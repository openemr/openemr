<?php

/**
 * DatabaseCheck - Verifies database connectivity
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com/
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Health\Check;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Health\HealthCheckInterface;
use OpenEMR\Health\HealthCheckResult;

class DatabaseCheck implements HealthCheckInterface
{
    public const NAME = 'database';

    public function getName(): string
    {
        return static::NAME;
    }

    public function check(): HealthCheckResult
    {
        try {
            QueryUtils::sqlStatementThrowException("SELECT 1", [], noLog: true);
            return new HealthCheckResult($this->getName(), true);
        } catch (\Throwable $e) {
            return new HealthCheckResult(
                $this->getName(),
                false,
                $e->getMessage(),
            );
        }
    }
}
