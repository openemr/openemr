<?php

declare(strict_types=1);

namespace OpenEMR\BC;

use ADODB_mysqli_log;
use mysqli;
use RuntimeException;
use OpenEMR\Common\Session\SessionWrapperInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @deprecated New code should use existing DB tooling and not directly create new connections.
 */
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

        $conn->ExecuteNoLog("SET NAMES '$config->charset'");
        // "Turn off STRICT SQL"
        $conn->ExecuteNoLog("SET sql_mode = ''");

        // Other paths may end up customizing this further.

        return $conn;
    }

    /**
     * @throws RuntimeException if a connection could not be established
     */
    public static function createMysqli(
        DatabaseConnectionOptions $config,
        bool $persistent,
    ): mysqli {
        $mysqli = new mysqli();
        $mysqli->options(MYSQLI_READ_DEFAULT_GROUP, 0);
        $mysqli->options(MYSQLI_OPT_LOCAL_INFILE, 1);

        $flags = 0;
        if ($config->sslCaPath !== null) {
            $flags = MYSQLI_CLIENT_SSL;
            $mysqli->ssl_set(
                key: $config->sslClientCert['key'] ?? null,
                certificate: $config->sslClientCert['cert'] ?? null,
                ca_certificate: $config->sslCaPath,
                ca_path: null,
                cipher_algos: null,
            );
        }

        // TODO: Sockets support (do all paths at once)

        $host = $persistent ? sprintf('p:%s', $config->host) : $config->host;

        $success = $mysqli->real_connect(
            hostname: $host,
            username: $config->user,
            password: $config->password,
            database: $config->dbname,
            port: $config->port,
            flags: $flags,
        );

        if (!$success) {
            throw new RuntimeException(
                sprintf('Could not connect to the database (%s)',  $mysqli->connect_error),
                $mysqli->connect_errno,
            );
        }

        // This is preferred over SET NAMES since it also influences escaping
        $mysqli->set_charset($config->charset);
        $mysqli->query("SET sql_mode = ''");
        return $mysqli;
    }

    public static function detectConnectionPersistence(
        ParameterBag $globals,
        SessionWrapperInterface $session,
    ): bool {
        if ($globals->getBoolean('connection_pooling_off')) {
            return false;
        }
        if ($globals->getBoolean('enable_database_connection_pooling')) {
            return true;
        }
        if (!empty($session->get('enable_database_connection_pooling'))) {
            return true;
        }
        return false;
    }

    /**
     * @deprecated Relies on global state; prefer explicit configuration
     */
    public static function detectConnectionPersistenceFromGlobalState(): bool
    {
        // If connection pooling is explicitly disabled, return false
        if (!empty($GLOBALS['connection_pooling_off'])) {
            return false;
        }

        // Check if pooling is enabled via globals or session
        if (!empty($GLOBALS['enable_database_connection_pooling'])) {
            return true;
        }
        if (!empty($_SESSION['enable_database_connection_pooling'])) {
            return true;
        }

        return false;
    }

    private static function loadAdodbClasses(): void
    {
        $root = dirname(__DIR__, 2);
        $adoDir = $root . '/vendor/adodb/adodb-php';
        // adodb.inc.php is in composer autoload_files path
        require_once $adoDir . '/drivers/adodb-mysqli.inc.php';
        // adodb-pager.inc.php was included but seems never used
        require_once $root . '/library/ADODB_mysqli_log.php';
    }
}
