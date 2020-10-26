<?php

/** @package verysimple::DB::DataDriver */

require_once("IDataDriver.php");
require_once("verysimple/DB/ISqlFunction.php");
require_once("verysimple/DB/DatabaseException.php");
require_once("verysimple/DB/DatabaseConfig.php");

/**
 * An implementation of IDataDriver that communicates with
 * a MySQL server.
 * This is one of the native drivers
 * supported by Phreeze
 *
 * @package verysimple::DB::DataDriver
 * @author VerySimple Inc. <noreply@verysimple.com>
 * @copyright 1997-2010 VerySimple Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.0
 */
class DataDriverMySQLi implements IDataDriver
{
    /** @var characters that will be escaped */
    static $BAD_CHARS = array (
            "\\",
            "\0",
            "\n",
            "\r",
            "\x1a",
            "'",
            '"'
    );

    /** @var characters that will be used to replace bad chars */
    static $GOOD_CHARS = array (
            "\\\\",
            "\\0",
            "\\n",
            "\\r",
            "\Z",
            "\'",
            '\"'
    );

    /**
     * @inheritdocs
     */
    function GetServerType()
    {
        return "MySQLi";
    }
    function Ping($connection)
    {
        return mysqli_ping($connection);
    }

    /**
     * @inheritdocs
     */
    function Open($connectionstring, $database, $username, $password, $charset = '', $bootstrap = '')
    {
        if (! function_exists("mysqli_connect")) {
            throw new DatabaseException('mysqli extension is not enabled on this server.', DatabaseException::$CONNECTION_ERROR);
        }

            // if the port is provided in the connection string then strip it out and provide it as a separate param
        $hostAndPort = explode(":", $connectionstring);
        $host = $hostAndPort [0];
        $port = count($hostAndPort) > 1 ? $hostAndPort [1] : null;

        if ((!empty($GLOBALS["enable_database_connection_pooling"]) || !empty($_SESSION["enable_database_connection_pooling"])) && empty($GLOBALS['connection_pooling_off'])) {
            $host = "p:" . $host;
        }

        $connection = @mysqli_init();
        if (is_null($connection)) {
            throw new DatabaseException("Error connecting to database: " . mysqli_connect_error(), DatabaseException::$CONNECTION_ERROR);
        }

        //Below was added by OpenEMR to support mysql ssl
        $mysqlSsl = false;
        if (defined('MYSQLI_CLIENT_SSL') && file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-ca")) {
            $mysqlSsl = true;
            if (
                file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-key") &&
                file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-cert")
            ) {
                // with client side certificate/key
                mysqli_ssl_set(
                    $connection,
                    "${GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-key",
                    "${GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-cert",
                    "${GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-ca",
                    null,
                    null
                );
            } else {
                // without client side certificate/key
                mysqli_ssl_set(
                    $connection,
                    null,
                    null,
                    "${GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-ca",
                    null,
                    null
                );
            }
        }
        if ($mysqlSsl) {
            $ok = mysqli_real_connect(
                $connection,
                $host,
                $username,
                $password,
                $database,
                $port,
                null,
                MYSQLI_CLIENT_SSL
            );
        } else {
            $ok = mysqli_real_connect(
                $connection,
                $host,
                $username,
                $password,
                $database,
                $port
            );
        }

        if (!$ok) {
            throw new DatabaseException("Error connecting to database: " . mysqli_connect_error(), DatabaseException::$CONNECTION_ERROR);
        }

        if ($charset) {
            mysqli_set_charset($connection, $charset);

            if (mysqli_connect_errno()) {
                throw new DatabaseException("Unable to set charset: " . mysqli_connect_error(), DatabaseException::$CONNECTION_ERROR);
            }
        }

        if ($bootstrap) {
            $statements = explode(';', $bootstrap);
            foreach ($statements as $sql) {
                try {
                    $this->Execute($connection, $sql);
                } catch (Exception $ex) {
                    throw new DatabaseException("problem with bootstrap sql: " . $ex->getMessage(), DatabaseException::$ERROR_IN_QUERY);
                }
            }
        }

        if (!empty($GLOBALS['debug_ssl_mysql_connection'])) {
            $sslTestCipher = mysqli_query($connection, "SHOW STATUS LIKE 'Ssl_cipher';");
            error_log("CHECK SSL CIPHER IN PATIENT PORTAL MYSQLI: " . htmlspecialchars(print_r(mysqli_fetch_assoc($sslTestCipher), true), ENT_QUOTES));
            mysqli_free_result($sslTestCipher);
        }

        return $connection;
    }

    /**
     * @inheritdocs
     */
    function Close($connection)
    {
        @mysqli_close($connection); // ignore warnings
    }

    /**
     * @inheritdocs
     */
    function Query($connection, $sql)
    {
        if (! $rs = @mysqli_query($connection, $sql)) {
            throw new DatabaseException(mysqli_error($connection), DatabaseException::$ERROR_IN_QUERY);
        }

        return $rs;
    }

    /**
     * @inheritdocs
     */
    function Execute($connection, $sql)
    {
        if (! $result = @mysqli_query($connection, $sql)) {
            throw new DatabaseException(mysqli_error($connection), DatabaseException::$ERROR_IN_QUERY);
        }

        return mysqli_affected_rows($connection);
    }

    /**
     * @inheritdocs
     */
    function Fetch($connection, $rs)
    {
        return mysqli_fetch_assoc($rs);
    }

    /**
     * @inheritdocs
     */
    function GetLastInsertId($connection)
    {
        return (mysqli_insert_id($connection));
    }

    /**
     * @inheritdocs
     */
    function GetLastError($connection)
    {
        return mysqli_error($connection);
    }

    /**
     * @inheritdocs
     */
    function Release($connection, $rs)
    {
        mysqli_free_result($rs);
    }

    /**
     * @inheritdocs
     * this method currently uses replacement and not mysqli_real_escape_string
     * so that a database connection is not necessary in order to escape.
     * this way cached queries can be used without connecting to the DB server
     */
    function Escape($val)
    {
        return str_replace(self::$BAD_CHARS, self::$GOOD_CHARS, $val);
        // return mysqli_real_escape_string($val);
    }

    /**
     * @inheritdocs
     */
    public function GetQuotedSql($val)
    {
        if ($val === null) {
            return DatabaseConfig::$CONVERT_NULL_TO_EMPTYSTRING ? "''" : 'NULL';
        }

        if ($val instanceof ISqlFunction) {
            return $val->GetQuotedSql($this);
        }

        return "'" . $this->Escape($val) . "'";
    }

    /**
     * @inheritdocs
     */
    function GetTableNames($connection, $dbname, $ommitEmptyTables = false)
    {
        $sql = "SHOW TABLE STATUS FROM `" . $this->Escape($dbname) . "`";
        $rs = $this->Query($connection, $sql);

        $tables = array ();

        while ($row = $this->Fetch($connection, $rs)) {
            if ($ommitEmptyTables == false || $rs ['Data_free'] > 0) {
                $tables [] = $row ['Name'];
            }
        }

        return $tables;
    }

    /**
     * @inheritdocs
     */
    function Optimize($connection, $table)
    {
        $result = "";
        $rs = $this->Query($connection, "optimize table `" . $this->Escape($table) . "`");

        while ($row = $this->Fetch($connection, $rs)) {
            $tbl = $row ['Table'];
            if (! isset($results [$tbl])) {
                $results [$tbl] = "";
            }

            $result .= trim($results [$tbl] . " " . $row ['Msg_type'] . "=\"" . $row ['Msg_text'] . "\"");
        }

        return $result;
    }

    /**
     * @inheritdocs
     */
    function StartTransaction($connection)
    {
        $this->Execute($connection, "SET AUTOCOMMIT=0");
        $this->Execute($connection, "START TRANSACTION");
    }

    /**
     * @inheritdocs
     */
    function CommitTransaction($connection)
    {
        $this->Execute($connection, "COMMIT");
        $this->Execute($connection, "SET AUTOCOMMIT=1");
    }

    /**
     * @inheritdocs
     */
    function RollbackTransaction($connection)
    {
        $this->Execute($connection, "ROLLBACK");
        $this->Execute($connection, "SET AUTOCOMMIT=1");
    }
}
