<?php

/**
 * Support for predis sentinel (via redis)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 *  Note: This class was developed with assistance from AI (Claude by Anthropic and ChatGPT by OpenAI)
 *        for code structure and implementation guidance.
*/

namespace OpenEMR\Common\Session\Predis;

use OpenEMR\BC\ServiceContainer;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

class SentinelUtil
{
    private static string $sentinelCa = 'redis-sentinel-ca';
    private static string $sentinelCert = 'redis-sentinel-cert';
    private static string $sentinelKey = 'redis-sentinel-key';
    private static string $masterCa = 'redis-master-ca';
    private static string $masterCert = 'redis-master-cert';
    private static string $masterKey = 'redis-master-key';
    private readonly string $sessionStorageMode;
    private readonly array $predisSentinels;
    private readonly string $predisMaster;
    private readonly ?string $predisSentinelsPassword;
    private readonly ?string $predisMasterPassword;

    private readonly bool $predisTls;
    private readonly bool $predisX509;

    private readonly ?string $predisSentinelCertKeyPath;
    private readonly ?string $sentinelCaFile;
    private readonly ?string $sentinelCertFile;
    private readonly ?string $sentinelKeyFile;
    private readonly ?string $masterCaFile;
    private readonly ?string $masterCertFile;
    private readonly ?string $masterKeyFile;

    private readonly int $redisSessionLockTtl;
    private readonly int $redisSessionLockMaxWait;

    private readonly LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? ServiceContainer::getLogger();

        if (!extension_loaded('redis')) {
            $this->logger->error("phpredis extension is not loaded.");
            throw new \RuntimeException("phpredis extension (ext-redis) is required for predis-sentinel session storage.");
        }

        // required to ensure running correct mode
        $this->sessionStorageMode = getenv('SESSION_STORAGE_MODE', true) ?? null;
        if ($this->sessionStorageMode !== 'predis-sentinel') {
            $this->logger->error("Invalid SESSION_STORAGE_MODE: " . $this->sessionStorageMode);
            throw new \Exception("Invalid SESSION_STORAGE_MODE. Expected 'predis-sentinel'.");
        }

        // required for listing of the sentinels (string delimited by |||)
        $predisSentinels = getenv('REDIS_SENTINELS', true) ?? null;
        if (empty($predisSentinels)) {
            $this->logger->error("REDIS_SENTINELS environment variable is not set.");
            throw new \Exception("REDIS_SENTINELS environment variable is not set.");
        }
        $this->predisSentinels = explode('|||', $predisSentinels);
        if (empty($this->predisSentinels)) {
            $this->logger->error("REDIS_SENTINELS unable to explode any elements using the ||| delimiter.");
            throw new \Exception("REDIS_SENTINELS unable to explode any elements using the ||| delimiter.");
        }

        // optional and will default to 'mymaster' if not provided
        $this->predisMaster = getenv('REDIS_MASTER', true) ?? 'mymaster';

        // optional if have a password for sentinels
        $this->predisSentinelsPassword = getenv('REDIS_SENTINELS_PASSWORD', true) ?? null;

        // optional if have a password for master/replicates
        $this->predisMasterPassword = getenv('REDIS_MASTER_PASSWORD', true) ?? null;

        // optional if using TLS
        $predisTls = getenv('REDIS_TLS', true) ?? null;
        $this->predisTls = ($predisTls === 'yes');

        // optional if using TLS with X509 certificate
        $predisX509 = getenv('REDIS_X509', true) ?? null;
        $this->predisX509 = ($predisX509 === 'yes');
        // note that TLS needs to be turned on if X509 is turned on
        if ($this->predisX509 && !$this->predisTls) {
            $this->logger->error("REDIS_TLS must be set to 'yes' if REDIS_X509 is set to 'yes'.");
            throw new \Exception("REDIS_TLS environment variable must be set to 'yes' if REDIS_X509 is set to 'yes'.");
        }

        // optional. If using TLS, then this is required.
        $this->predisSentinelCertKeyPath = getenv('REDIS_TLS_CERT_KEY_PATH', true) ?? null;
        if ($this->predisTls && empty($this->predisSentinelCertKeyPath)) {
            $this->logger->error("REDIS_TLS_CERT_KEY_PATH environment variable is required when REDIS_TLS is set to 'yes'.");
            throw new \Exception("REDIS_TLS_CERT_KEY_PATH environment variable is required when REDIS_TLS is set to 'yes'.");
        }

        // collect pertinent certificate files and ensure they are readable
        $this->sentinelCaFile = $this->predisTls ? $this->predisSentinelCertKeyPath . '/' . self::$sentinelCa : null;
        if (!empty($this->sentinelCaFile) && !is_readable($this->sentinelCaFile)) {
            $this->logger->error("Sentinel CA file does not exist or is not readable: " . $this->sentinelCaFile);
            throw new \Exception("Sentinel CA file does not exist or is not readable: " . $this->sentinelCaFile);
        }
        $this->sentinelCertFile = $this->predisX509 ? $this->predisSentinelCertKeyPath . '/' . self::$sentinelCert : null;
        if (!empty($this->sentinelCertFile) && !is_readable($this->sentinelCertFile)) {
            $this->logger->error("Sentinel certificate file does not exist or is not readable: " . $this->sentinelCertFile);
            throw new \Exception("Sentinel certificate file does not exist or is not readable: " . $this->sentinelCertFile);
        }
        $this->sentinelKeyFile = $this->predisX509 ? $this->predisSentinelCertKeyPath . '/' . self::$sentinelKey : null;
        if (!empty($this->sentinelKeyFile) && !is_readable($this->sentinelKeyFile)) {
            $this->logger->error("Sentinel key file does not exist or is not readable: " . $this->sentinelKeyFile);
            throw new \Exception("Sentinel key file does not exist or is not readable: " . $this->sentinelKeyFile);
        }
        $this->masterCaFile = $this->predisTls ? $this->predisSentinelCertKeyPath . '/' . self::$masterCa : null;
        if (!empty($this->masterCaFile) && !is_readable($this->masterCaFile)) {
            $this->logger->error("Master CA file does not exist or is not readable: " . $this->masterCaFile);
            throw new \Exception("Master CA file does not exist or is not readable: " . $this->masterCaFile);
        }
        $this->masterCertFile = $this->predisX509 ? $this->predisSentinelCertKeyPath . '/' . self::$masterCert : null;
        if (!empty($this->masterCertFile) && !is_readable($this->masterCertFile)) {
            $this->logger->error("Master certificate file does not exist or is not readable: " . $this->masterCertFile);
            throw new \Exception("Master certificate file does not exist or is not readable: " . $this->masterCertFile);
        }
        $this->masterKeyFile = $this->predisX509 ? $this->predisSentinelCertKeyPath . '/' . self::$masterKey : null;
        if (!empty($this->masterKeyFile) && !is_readable($this->masterKeyFile)) {
            $this->logger->error("Master key file does not exist or is not readable: " . $this->masterKeyFile);
            throw new \Exception("Master key file does not exist or is not readable: " . $this->masterKeyFile);
        }

        // optional: lock TTL in seconds (how long Redis holds the lock key before auto-expiry)
        $redisSessionLockTtl = getenv('REDIS_SESSION_LOCK_TTL', true);
        $this->redisSessionLockTtl = ($redisSessionLockTtl !== false && ctype_digit($redisSessionLockTtl) && (int) $redisSessionLockTtl > 0)
            ? (int) $redisSessionLockTtl
            : LockingRedisSessionHandler::DEFAULT_LOCK_TTL_SECONDS;

        // optional: max seconds to spin-wait for the lock before throwing
        $redisSessionLockMaxWait = getenv('REDIS_SESSION_LOCK_MAX_WAIT', true);
        $this->redisSessionLockMaxWait = ($redisSessionLockMaxWait !== false && ctype_digit($redisSessionLockMaxWait) && (int) $redisSessionLockMaxWait > 0)
            ? (int) $redisSessionLockMaxWait
            : LockingRedisSessionHandler::DEFAULT_LOCK_MAX_WAIT_SECONDS;

        $this->logger->debug("Predis Sentinel constructor initialized successfully.", [
            'predisSentinels' => $this->predisSentinels,
            'predisMaster' => $this->predisMaster,
            'predisSentinelsPassword' => !empty($this->predisSentinelsPassword) ? '***' : '',
            'predisMasterPassword' => !empty($this->predisMasterPassword) ? '***' : '',
            'predisTls' => $this->predisTls ? 'true' : 'false',
            'predisX509' => $this->predisX509 ? 'true' : 'false',
            'sentinelCaFile' => $this->sentinelCaFile,
            'sentinelCertFile' => $this->sentinelCertFile,
            'sentinelKeyFile' => $this->sentinelKeyFile,
            'masterCaFile' => $this->masterCaFile,
            'masterCertFile' => $this->masterCertFile,
            'masterKeyFile' => $this->masterKeyFile,
            'redisSessionLockTtl' => $this->redisSessionLockTtl,
            'redisSessionLockMaxWait' => $this->redisSessionLockMaxWait,
        ]);
    }

    /**
     * Query sentinels to discover the current master and return a connected \Redis client.
     *
     * Tries each sentinel in order.  The first one that responds with a valid
     * master address wins.  TLS / mTLS settings are applied to both the sentinel
     * query connections and the final master connection.
     */
    public function configureClient(): \Redis
    {
        [$masterHost, $masterPort] = $this->discoverMaster();

        $redis = new \Redis();

        $host = $this->predisTls ? 'tls://' . $masterHost : $masterHost;
        /** @var array{stream?: array<string, bool|string|null>} $options */
        $options = [];
        if ($this->predisTls) {
            $sslOptions = [
                'verify_peer'      => true,
                'verify_peer_name' => true,
                'cafile'           => $this->masterCaFile,
            ];
            if ($this->predisX509) {
                $sslOptions['local_cert'] = $this->masterCertFile;
                $sslOptions['local_pk']   = $this->masterKeyFile;
            }
            $options['stream'] = $sslOptions;
        }

        $connected = $redis->connect(
            $host,
            $masterPort,
            3.0,   // connectTimeout
            null,  // persistentId
            0,     // retryInterval
            3.0,   // readTimeout
            $options,
        );

        if (!$connected) {
            throw new \RuntimeException(sprintf(
                'Failed to connect to Redis master at %s:%d',
                $masterHost,
                $masterPort,
            ));
        }

        if (!empty($this->predisMasterPassword)) {
            $redis->auth($this->predisMasterPassword);
        }

        return $redis;
    }

    public function configure(int $ttl): \SessionHandlerInterface
    {
        $redis = $this->configureClient();
        $inner = new RedisSessionHandler($redis, ['ttl' => $ttl]);
        return new LockingRedisSessionHandler($redis, $inner, $this->redisSessionLockTtl, $this->redisSessionLockMaxWait);
    }

    /**
     * Query each sentinel until one returns the master address.
     *
     * @return array{0: string, 1: int} [host, port]
     */
    private function discoverMaster(): array
    {
        $lastException = null;

        foreach ($this->predisSentinels as $sentinelHost) {
            try {
                $host = $this->predisTls ? 'tls://' . trim($sentinelHost) : trim($sentinelHost);

                // phpredis 6.0+ supports an array constructor for RedisSentinel with
                // TLS options.  PHPStan stubs only know the older positional constructor.
                // phpredis 6.0+ array constructor; stubs only know positional form
                $sentinel = new \RedisSentinel([ // @phpstan-ignore arguments.count, argument.type
                    'host'           => $host,
                    'port'           => 26379,
                    'connectTimeout' => 3.0,
                    'readTimeout'    => 3.0,
                ] + $this->buildSentinelAuthOptions() + $this->buildSentinelSslOptions());

                $masterInfo = $sentinel->getMasterAddrByName($this->predisMaster);

                if (
                    is_array($masterInfo)
                    && isset($masterInfo[0], $masterInfo[1])
                    && is_string($masterInfo[0])
                    && (is_string($masterInfo[1]) || is_int($masterInfo[1]))
                ) {
                    $masterHost = $masterInfo[0];
                    $masterPort = (int) $masterInfo[1];
                    $this->logger->debug('Discovered Redis master via sentinel', [
                        'sentinel' => $sentinelHost,
                        'master'   => $masterHost . ':' . $masterPort,
                    ]);
                    return [$masterHost, $masterPort];
                }
            } catch (\RedisException $e) {
                $lastException = $e;
                $this->logger->warning('Sentinel query failed, trying next', [
                    'sentinel' => $sentinelHost,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        throw new \RuntimeException(
            'Could not discover Redis master from any sentinel: '
            . ($lastException !== null ? $lastException->getMessage() : 'all sentinels returned empty'),
            0,
            $lastException,
        );
    }

    /**
     * @return array<string, string>
     */
    private function buildSentinelAuthOptions(): array
    {
        if (!empty($this->predisSentinelsPassword)) {
            return ['auth' => $this->predisSentinelsPassword];
        }
        return [];
    }

    /**
     * @return array<string, array<string, bool|string|null>>
     */
    private function buildSentinelSslOptions(): array
    {
        if (!$this->predisTls) {
            return [];
        }

        $sslOptions = [
            'verify_peer'      => true,
            'verify_peer_name' => true,
            'cafile'           => $this->sentinelCaFile,
        ];
        if ($this->predisX509) {
            $sslOptions['local_cert'] = $this->sentinelCertFile;
            $sslOptions['local_pk']   = $this->sentinelKeyFile;
        }
        return ['ssl' => $sslOptions];
    }
}
