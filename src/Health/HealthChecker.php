<?php

/**
 * HealthChecker - Runs all health checks and aggregates results
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com/
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Health;

use OpenEMR\Health\Check\CacheCheck;
use OpenEMR\Health\Check\DatabaseCheck;
use OpenEMR\Health\Check\FilesystemCheck;
use OpenEMR\Health\Check\OAuthKeysCheck;
use OpenEMR\Health\Check\SessionCheck;

class HealthChecker
{
    /** @var HealthCheckInterface[] */
    private array $checks = [];

    public function __construct()
    {
        $this->registerDefaultChecks();
    }

    private function registerDefaultChecks(): void
    {
        $this->addCheck(new DatabaseCheck());
        $this->addCheck(new FilesystemCheck());
        $this->addCheck(new SessionCheck());
        $this->addCheck(new OAuthKeysCheck());
        $this->addCheck(new CacheCheck());
    }

    public function addCheck(HealthCheckInterface $check): void
    {
        $this->checks[] = $check;
    }

    /**
     * Run all health checks
     *
     * @return HealthCheckResult[]
     */
    public function runAll(): array
    {
        $results = [];
        foreach ($this->checks as $check) {
            $results[] = $check->check();
        }
        return $results;
    }

    /**
     * Get results as an associative array suitable for JSON response
     */
    public function getResultsArray(): array
    {
        $checks = [];
        foreach ($this->runAll() as $result) {
            $checks[$result->name] = $result->healthy;
        }
        return [
            'status' => 'ready',
            'checks' => $checks,
        ];
    }
}
