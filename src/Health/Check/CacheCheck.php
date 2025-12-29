<?php

/**
 * CacheCheck - Verifies Redis cache connectivity (if configured)
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
use Predis\Client;

class CacheCheck implements HealthCheckInterface
{
    public function getName(): string
    {
        return 'cache';
    }

    public function check(): HealthCheckResult
    {
        try {
            $sessionStorageMode = getenv('SESSION_STORAGE_MODE') ?: 'file';

            // If not using Redis, cache check is not applicable - report as healthy
            if ($sessionStorageMode !== 'predis-sentinel') {
                return new HealthCheckResult($this->getName(), true, 'Not configured');
            }

            // Verify Redis sentinels are configured
            $sentinels = getenv('REDIS_SENTINELS');
            if ($sentinels === false || $sentinels === '') {
                return new HealthCheckResult(
                    $this->getName(),
                    false,
                    'Redis sentinels not configured'
                );
            }

            // Parse sentinel configuration - filter out empty strings
            $sentinelHosts = array_filter(explode('|||', $sentinels), fn($s): bool => $s !== '');
            if (count($sentinelHosts) === 0) {
                return new HealthCheckResult(
                    $this->getName(),
                    false,
                    'Invalid Redis sentinel configuration'
                );
            }

            $masterName = getenv('REDIS_MASTER') ?: 'mymaster';

            // Build sentinel parameters
            $sentinelParameters = array_map(
                fn($sentinel): array => ['host' => $sentinel],
                $sentinelHosts
            );

            $options = [
                'replication' => 'sentinel',
                'service' => $masterName,
            ];

            $password = getenv('REDIS_MASTER_PASSWORD');
            if ($password !== false && $password !== '') {
                $options['parameters'] = ['password' => $password];
            }

            // Try to connect and ping
            $redis = new Client($sentinelParameters, $options);
            $response = $redis->ping();

            if ($response->getPayload() !== 'PONG') {
                return new HealthCheckResult(
                    $this->getName(),
                    false,
                    'Redis ping failed'
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
