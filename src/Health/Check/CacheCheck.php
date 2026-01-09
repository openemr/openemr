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
    public const NAME = 'cache';

    public function getName(): string
    {
        return static::NAME;
    }

    public function check(): HealthCheckResult
    {
        try {
            // Check for direct Redis connection (ElastiCache/AWS pattern)
            $directResult = $this->checkDirectRedis();
            if ($directResult !== null) {
                return $directResult;
            }

            // Check for Redis Sentinel mode
            $sentinelResult = $this->checkRedisSentinel();
            if ($sentinelResult !== null) {
                return $sentinelResult;
            }

            // No Redis configured - report as healthy
            return new HealthCheckResult($this->getName(), true, 'Not configured');
        } catch (\Throwable $e) {
            return new HealthCheckResult(
                $this->getName(),
                false,
                $e->getMessage()
            );
        }
    }

    /**
     * Check direct Redis connection (used by ElastiCache, standalone Redis)
     */
    private function checkDirectRedis(): ?HealthCheckResult
    {
        $redisServer = getenv('REDIS_SERVER');
        if ($redisServer === false || $redisServer === '') {
            return null;
        }

        $redisPort = getenv('REDIS_PORT') ?: '6379';
        $redisTls = getenv('REDIS_TLS') === 'yes';
        $redisPassword = getenv('REDIS_PASSWORD') ?: null;

        $parameters = [
            'scheme' => $redisTls ? 'tls' : 'tcp',
            'host' => $redisServer,
            'port' => (int)$redisPort,
        ];

        if ($redisPassword !== null && $redisPassword !== '') {
            $parameters['password'] = $redisPassword;
        }

        if ($redisTls) {
            $parameters['ssl'] = [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ];
        }

        $redis = new Client($parameters);
        $response = $redis->ping();

        if ($response->getPayload() !== 'PONG') {
            return new HealthCheckResult(
                $this->getName(),
                false,
                'Redis ping failed'
            );
        }

        return new HealthCheckResult($this->getName(), true);
    }

    /**
     * Check Redis Sentinel connection
     */
    private function checkRedisSentinel(): ?HealthCheckResult
    {
        $sessionStorageMode = getenv('SESSION_STORAGE_MODE') ?: 'file';
        if ($sessionStorageMode !== 'predis-sentinel') {
            return null;
        }

        $sentinels = getenv('REDIS_SENTINELS');
        if ($sentinels === false || $sentinels === '') {
            return new HealthCheckResult(
                $this->getName(),
                false,
                'Redis sentinels not configured'
            );
        }

        $sentinelHosts = array_filter(explode('|||', $sentinels), fn($s): bool => $s !== '');
        if (count($sentinelHosts) === 0) {
            return new HealthCheckResult(
                $this->getName(),
                false,
                'Invalid Redis sentinel configuration'
            );
        }

        $masterName = getenv('REDIS_MASTER') ?: 'mymaster';

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
    }
}
