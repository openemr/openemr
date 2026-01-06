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

use OpenEMR\Core\OEGlobalsBag;
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
            $globals = OEGlobalsBag::getInstance();
            $adodb = $globals->get('adodb');

            if (!is_array($adodb) || !isset($adodb['db'])) {
                return new HealthCheckResult(
                    $this->getName(),
                    false,
                    'Database connection not initialized'
                );
            }

            $db = $adodb['db'];
            $result = $db->Execute("SELECT 1");

            if ($result === false) {
                return new HealthCheckResult(
                    $this->getName(),
                    false,
                    'Failed to execute query'
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
