<?php

/**
* Support for predis sentinel (via redis)
*
* @package   OpenEMR
* @link      https://www.open-emr.org
* @author    Brady Miller <brady.g.miller@gmail.com>
* @copyright Copyright (c) 2025 Brady Miller <brady.g.miller@gmail.com>
* @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

namespace OpenEMR\Common\Session\Predis;

use OpenEMR\Common\Session\Predis\PredisSessionHandler;
use Predis\Client;

class SentinelUtil
{
    private static $sentinelCa = 'predis-sentinel-ca';
    private static $sentinelCert = 'predis-sentinel-cert';
    private static $sentinelKey = 'predis-sentinel-key';
    private static $masterCa = 'predis-master-ca';
    private static $masterCert = 'predis-master-cert';
    private static $masterKey = 'predis-master-key';

    private int $ttl;
    private string $sessionStorageMode;
    private array $predisSentinels;
    private string $predisMaster;
    private ?string $predisSentinelsPassword;
    private ?string $predisMasterPassword;

    private bool $predisTls;
    private bool $predisX509;

    private ?string $predisSentinelCertKeyPath;
    private ?string $sentinelCaFile;
    private ?string $sentinelCertFile;
    private ?string $sentinelKeyFile;
    private ?string $masterCaFile;
    private ?string $masterCertFile;
    private ?string $masterKeyFile;

    public function __construct(int $ttl)
    {
        // Collect and validate environment variables
        $this->ttl = $ttl;

        // required to ensure running correct mode
        $this->sessionStorageMode = getenv('SESSION_STORAGE_MODE', true) ?? null;
        if ($this->sessionStorageMode !== 'predis-sentinel') {
            throw new \Exception("Invalid SESSION_STORAGE_MODE. Expected 'predis-sentinel'.");
        }

        // required for listing of the sentinels (string delimited by |||)
        $predisSentinels = getenv('PREDIS_SENTINELS', true) ?? null;
        if (empty($predisSentinels)) {
            throw new \Exception("PREDIS_SENTINELS environment variable is not set.");
        }
        $this->predisSentinels = explode('|||', $predisSentinels);
        if (empty($this->predisSentinels)) {
            throw new \Exception("PREDIS_SENTINELS unable to explode any elements using the ||| delimiter.");
        }

        // optional and will default to 'mymaster' if not provided
        $this->predisMaster = getenv('PREDIS_MASTER', true) ?? 'mymaster';

        // optional if have a password for sentinels
        $this->predisSentinelsPassword = getenv('PREDIS_SENTINELS_PASSWORD', true) ?? null;

        // optional if have a password for master/replicates
        $this->predisMasterPassword = getenv('PREDIS_MASTER_PASSWORD', true) ?? null;

        // optional if using TLS
        $predisTls = getenv('PREDIS_TLS', true) ?? null;
        $this->predisTls = ($predisTls === 'yes');

        // optional if using TLS with X509 certificate
        $predisX509 = getenv('PREDIS_X509', true) ?? null;
        $this->predisX509 = ($predisX509 === 'yes');
        // note that TLS needs to be turned on if X509 is turned on
        if ($this->predisX509 && !$this->predisTls) {
            throw new \Exception("PREDIS_TLS environment variable must be set to 'yes' if PREDIS_X509 is set to 'yes'.");
        }

        // optional. If using TLS, then this is required.
        $this->predisSentinelCertKeyPath = getenv('PREDIS_TLS_CERT_KEY_PATH', true) ?? null;
        if ($this->predisTls && empty($this->predisSentinelCertKeyPath)) {
            throw new \Exception("PREDIS_TLS_CERT_KEY_PATH environment variable is required when PREDIS_TLS is set to 'yes'.");
        }

        // collect pertinent certificate files and ensure they are readable
        $this->sentinelCaFile = $this->predisTls ? $this->predisSentinelCertKeyPath . '/' . self::$sentinelCa : null;
        if (!empty($this->sentinelCaFile) && !is_readable($this->sentinelCaFile)) {
            throw new \Exception("Sentinel CA file does not exist or is not readable: " . $this->sentinelCaFile);
        }
        $this->sentinelCertFile = $this->predisX509 ? $this->predisSentinelCertKeyPath . '/' . self::$sentinelCert : null;
        if (!empty($this->sentinelCertFile) && !is_readable($this->sentinelCertFile)) {
            throw new \Exception("Sentinel certificate file does not exist or is not readable: " . $this->sentinelCertFile);
        }
        $this->sentinelKeyFile = $this->predisX509 ? $this->predisSentinelCertKeyPath . '/' . self::$sentinelKey : null;
        if (!empty($this->sentinelKeyFile) && !is_readable($this->sentinelKeyFile)) {
            throw new \Exception("Sentinel key file does not exist or is not readable: " . $this->sentinelKeyFile);
        }
        $this->masterCaFile = $this->predisTls ? $this->predisSentinelCertKeyPath . '/' . self::$masterCa : null;
        if (!empty($this->masterCaFile) && !is_readable($this->masterCaFile)) {
            throw new \Exception("Master CA file does not exist or is not readable: " . $this->masterCaFile);
        }
        $this->masterCertFile = $this->predisX509 ? $this->predisSentinelCertKeyPath . '/' . self::$masterCert : null;
        if (!empty($this->masterCertFile) && !is_readable($this->masterCertFile)) {
            throw new \Exception("Master certificate file does not exist or is not readable: " . $this->masterCertFile);
        }
        $this->masterKeyFile = $this->predisX509 ? $this->predisSentinelCertKeyPath . '/' . self::$masterKey : null;
        if (!empty($this->masterKeyFile) && !is_readable($this->masterKeyFile)) {
            throw new \Exception("Master key file does not exist or is not readable: " . $this->masterKeyFile);
        }
    }

    public function configure(): void
    {
        $useTls = $this->predisTls;
        $useClientCert = $this->predisX509;
        $sentinelCaFile = $this->sentinelCaFile;
        $sentinelCertFile = $this->sentinelCertFile;
        $sentinelKeyFile = $this->sentinelKeyFile;
        $sentinelPassword = $this->predisSentinelsPassword;

        $sentinelParameters = array_map(function ($host) use ($useTls, $useClientCert, $sentinelCaFile, $sentinelCertFile, $sentinelKeyFile, $sentinelPassword) {
            $parameters = [
                'scheme' => $useTls ? 'tls' : 'tcp',
                'host'   => $host,
                'port'   => 26379,
            ];

            if ($useTls) {
                $sslOptions = [
                    'verify_peer'       => true,
                    'verify_peer_name'  => true,
                    'cafile'            => $sentinelCaFile,
                ];

                if ($useClientCert) {
                    $sslOptions['local_cert'] = $sentinelCertFile;
                    $sslOptions['local_pk']   = $sentinelKeyFile;
                }

                $parameters['ssl'] = $sslOptions;
            }

            if (!empty($sentinelPassword)) {
                $parameters['password'] = $sentinelPassword;
            }

            return $parameters;
        }, $this->predisSentinels);

        // Define options for Sentinel
        $options = [
            'replication' => 'sentinel',
            'service'     => $this->predisMaster
        ];

        $parameters = [];
        if (!empty($this->predisMasterPassword)) {
            $parameters['password'] = $this->predisMasterPassword;
        }
        if ($useTls) {
            $parameters['scheme'] = 'tls';
            $sslOptions = [
                'verify_peer'       => true,
                'verify_peer_name'  => true,
                'cafile'            => $this->masterCaFile,
            ];

            if ($useClientCert) {
                $sslOptions['local_cert'] = $this->masterCertFile;
                $sslOptions['local_pk']   = $this->masterKeyFile;
            }

            $parameters['ssl'] = $sslOptions;
        }

        if (!empty($parameters)) {
            $options['parameters'] = $parameters;
        }

        // Create a new Predis client instance
        $redis = new Client($sentinelParameters, $options);

        // Initialize and register the session handler
        $handler = new PredisSessionHandler($redis, $this->ttl, 60, 70, 150000);
        $success = session_set_save_handler($handler, true);
        if (!$success) {
            throw new \Exception("Failed to set session handler for Predis Sentinel.");
        }
    }
}
