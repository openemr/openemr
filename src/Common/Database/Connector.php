<?php

/**
 * This singleton class provides a pooled Doctrine connection to consumers. All connection data
 * is configurable via sqlconf.php.
 *
 * If needed, the instance can be used in a transactional context:
 * <code>
 *     $database = \common\database\Connector::Instance();
 *     $entityManager = $database->entityManager;
 *     $entityManager->getConnection()->beginTransaction();
 *     try {
 *         // Entity work here...
 *         $entityManager->persist($someEntityToBePersisted);
 *         $entityManager->flush();
 *         $entityManager->getConnection()->commit();
 *     } catch (Exception $e) {
 *         $entityManager->getConnection()->rollBack();
 *         throw $e;
 *     }
 * </code>
 *
 * Copyright (C) 2016 Matthew Vita <matthewvita48@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\Common\Database;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use OpenEMR\Common\Database\Auditor;
use OpenEMR\Common\Logging\Logger;

final class Connector
{
    /**
     * The pooled Doctrine connection.
     */
    public $entityManager;

    /**
     * Logger for noting connection/configuration information.
     */
    private $logger;

    /**
     * Default constructor.
     */
    private function __construct()
    {
        $this->logger = new Logger("\OpenEMR\Common\Database\Connector");
        $this->createConnection();
    }

    /**
     * The only public method for consumers to either create or gain access to the sole singleton
     * instance of the class.
     *
     * @return Connector instance
     */
    public static function Instance()
    {
        static $singletonInstance = null;
        if ($singletonInstance === null) {
            $singletonInstance = new Connector();

            if ($GLOBALS['debug_ssl_mysql_connection']) {
                // below is to debug mysql ssl connection
                error_log("CHECK SSL CIPHER IN DOCTRINE: " . errorLogEscape(print_r($singletonInstance->entityManager->getConnection()->query("SHOW STATUS LIKE 'Ssl_cipher';")->FetchAll(), true)));
            }
        }

        return $singletonInstance;
    }

    /**
     * Creates the Doctrine connection. All connection data is configurable via sqlconf.php.
     * Pooling os controlled by $GLOBALS["enable_database_connection_pooling"] set in Globals at
     * 'Enable Database Connection Pooling' setting.
     *
     * @todo document throwables
     */
    private function createConnection()
    {
        global $sqlconf;
        $entityPath = array(__DIR__ . "../entities");

        if ($GLOBALS["enable_database_connection_pooling"]) {
            $doctrinePooling = true;
        } else {
            $doctrinePooling = false;
        }

        $this->logger->trace("Connecting with " . ($doctrinePooling ? "pooled" : "non-pooled") . " mode");
        $connection = array(
            'driver'   => "pdo_mysql",
            'host'     => $sqlconf["host"],
            'port'     => $sqlconf["port"],
            'user'     => $sqlconf["login"],
            'password' => $sqlconf["pass"],
            'dbname'   => $sqlconf["dbase"],
            'pooled'   => $doctrinePooling
        );

        global $disable_utf8_flag;

        $driverOptionsString = '';

        if (!$disable_utf8_flag) {
            if ($sqlconf['db_encoding'] == "utf8mb4") {
                $this->logger->trace("Enabling utf8mb4");
                $connection['charset'] = 'utf8mb4';
                $driverOptionsString = 'SET NAMES utf8mb4';
            } else {
                $this->logger->trace("Enabling utf8");
                $connection['charset'] = 'utf8';
                $driverOptionsString = 'SET NAMES utf8';
            }
        }

        $this->logger->trace("Clearing sql mode");
        if (!empty($driverOptionsString)) {
            $driverOptionsString .= ',sql_mode = \'\'';
        } else {
            $driverOptionsString = 'SET sql_mode = \'\'';
        }

        $this->logger->trace("Setting time zone");
        $driverOptionsString .= ", time_zone = '" . (new \DateTime())->format("P") . "'";

        // 1002 is the integer value of PDO::MYSQL_ATTR_INIT_COMMAND, which is
        // executed when connecting to the MySQL server. Note if utf8 or sql
        // mode commands fail, the connection will not be made.
        $connection['driverOptions'] = array(
            1002 => $driverOptionsString
        );

        // Set mysql to use ssl, if applicable.
        // Can support basic encryption by including just the mysql-ca pem (this is mandatory for ssl)
        // Can also support client based certificate if also include mysql-cert and mysql-key (this is optional for ssl)
        if (file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-ca")) {
            $connection['driverOptions'][\PDO::MYSQL_ATTR_SSL_CA ] = $GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-ca";
            if (
                file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-key") &&
                file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-cert")
            ) {
                $connection['driverOptions'][\PDO::MYSQL_ATTR_SSL_KEY] = $GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-key";
                $connection['driverOptions'][\PDO::MYSQL_ATTR_SSL_CERT] = $GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-cert";
            }
        }

        $this->logger->trace("Wiring up Doctrine entities");

        // Note that we need to turn on isDevMode or else it breaks if a user has redis extension installed in PHP, but doesn't have
        //  redis working from the localhost.
        // TODO : support false for isDevMode and thus support caching (and prevent issue with redis described above)
        $configuration = Setup::createAnnotationMetadataConfiguration($entityPath, true, null, null, false);
        $configuration->setAutoGenerateProxyClasses(true);

        $this->logger->trace("Creating connection");
        $this->entityManager = EntityManager::create($connection, $configuration);

        $this->logger->trace("Wiring up SQL auditor to store audit entries in `log` table");
        $this->entityManager->getConfiguration()->setSQLLogger(new Auditor());
    }
}
