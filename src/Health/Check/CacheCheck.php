<?php

/**
 * CacheCheck - Verifies Redis cache connectivity (if configured)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Health\Check;

use OpenEMR\Common\Session\Predis\SentinelUtil;
use OpenEMR\Health\HealthCheckInterface;
use OpenEMR\Health\HealthCheckResult;

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

        $redis = new \Redis();
        $host = $redisTls ? 'tls://' . $redisServer : $redisServer;
        $options = [];
        if ($redisTls) {
            $certKeyPath = getenv('REDIS_TLS_CERT_KEY_PATH') ?: '';
            $sslOptions = [
                'verify_peer'      => true,
                'verify_peer_name' => true,
            ];
            if ($certKeyPath !== '') {
                $sslOptions['cafile'] = $certKeyPath . '/redis-master-ca';
            }
            if (getenv('REDIS_X509') === 'yes' && $certKeyPath !== '') {
                $sslOptions['local_cert'] = $certKeyPath . '/redis-master-cert';
                $sslOptions['local_pk']   = $certKeyPath . '/redis-master-key';
            }
            $options['stream'] = $sslOptions;
        }

        $redis->connect($host, (int) $redisPort, 3.0, null, 0, 3.0, $options);

        if ($redisPassword !== null && $redisPassword !== '') {
            $redis->auth($redisPassword);
        }

        $pong = $redis->ping();
        if ($pong !== true) {
            return new HealthCheckResult(
                $this->getName(),
                false,
                'Redis ping failed'
            );
        }

        return new HealthCheckResult($this->getName(), true);
    }

    /**
     * Check Redis Sentinel connection.
     *
     * Reuses SentinelUtil::configureClient() so TLS / mTLS settings are
     * automatically picked up from the environment.
     */
    private function checkRedisSentinel(): ?HealthCheckResult
    {
        $sessionStorageMode = getenv('SESSION_STORAGE_MODE') ?: 'file';
        if ($sessionStorageMode !== 'predis-sentinel') {
            return null;
        }

        $redis = (new SentinelUtil())->configureClient();
        $pong = $redis->ping();

        if ($pong !== true) {
            return new HealthCheckResult(
                $this->getName(),
                false,
                'Redis ping failed'
            );
        }

        return new HealthCheckResult($this->getName(), true);
    }
}
