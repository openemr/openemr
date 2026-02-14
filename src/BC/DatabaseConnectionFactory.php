<?php

declare(strict_types=1);

namespace OpenEMR\BC;

use ADODB_mysqli_log;

class DatabaseConnectionFactory
{
    public static function createAdodb(
        DatabaseConnectionOptions $config,
        bool $persistent = false,
    ): ADODB_mysqli_log {
        self::loadAdodbClasses();
        $conn = ADONewConnection('mysqli_log');
        if ($conn === false) {
            throw new \Exception('SUPER BROKEN');
        }
        assert($conn instanceof ADODB_mysqli_log);

        // These were settings applied throughout the app. Not 100% clear if
        // they're still required.
        $conn->setConnectionParameter(MYSQLI_READ_DEFAULT_GROUP, 0);
        $conn->setConnectionParameter(MYSQLI_OPT_LOCAL_INFILE, 1);

        if ($config->sslCaPath !== null) {
            $conn->clientFlags = MYSQLI_CLIENT_SSL;
            $conn->ssl_ca = $config->sslCaPath;
            $conn->ssl_cert = $config->sslClientCert['cert'] ?? null;
            $conn->ssl_key = $config->sslClientCert['key'] ?? null;
        }

        // Sockets? It's supported on paper but unclear now to configure.
        assert($config->host !== null);

        $conn->port = $config->port;
        if ($persistent) {
            $conn->PConnect(
                argHostname: $config->host,
                argUsername: $config->user,
                argPassword: $config->password,
                argDatabaseName: $config->dbname,
            );
        } else {
            $conn->Connect(
                argHostname: $config->host,
                argUsername: $config->user,
                argPassword: $config->password,
                argDatabaseName: $config->dbname,
            );
        }

        // Configure the charset. This doesn't precisely match the previous
        // behavior, but should handle any current installation and not disrupt
        // upgrades.
        $charset = strtolower($config->charset);
        if ($charset !== 'utf8mb4') {
            // You're running something pretty weird and probably broken. Emit
            // a clear warning.
            if ($charset !== 'utf8') {
                trigger_error(
                    'DB configured to use an unsupported character set. ' .
                    'Only `utf8mb4` (preferred) and `utf8` (discouraged) are supported. ' .
                    'Ignoring configuration and using `utf8`.',
                    E_USER_DEPRECATED,
                );
            }
            $charset = 'utf8';
        }
        $conn->ExecuteNoLog("SET NAMES '$charset'");
        // "Turn off STRICT SQL"
        $conn->ExecuteNoLog("SET sql_mode = ''");

        // Other paths may end up customizing this further.

        return $conn;
    }

    private static function loadAdodbClasses(): void
    {
        $root = dirname(__DIR__, 2);
        $dir = $root . '/vendor/adodb/adodb-php';
        // require_once $dir . '/adodb.inc.php';
        // adodb is in composer autoload_files path
        // adodb-pager.inc.php?
        require_once $dir . '/drivers/adodb-mysqli.inc.php';
        require_once $root . '/library/ADODB_mysqli_log.php';
    }
}
