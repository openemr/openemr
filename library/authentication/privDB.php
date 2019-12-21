<?php
/**
 * To support an optional higher level of security, queries that access password
 * related information use these functions instead of the standard functions
 * provided by sql.inc.
 *
 * By default, the privQuery and privStatement calls pass-through to
 * the existing ADODB instance initialized by sql.inc.
 *
 * If an additional configuration file is created (secure_sqlconf.php) and saved
 * in the sites/<sitename> directory (e.g. sites/default).  The MySQL login
 * information defined in that file as $secure_* will be used to create an ADODB
 * instance specifically for querying privileged information.
 *
 * By configuring a server in this way, the default MySQL user can be denied access
 * to sensitive tables (currently only "users_secure" would qualify).  Thus
 * the likelyhood of unintended modification can be reduced (e.g. through SQL Injection).
 *
 * Details on how to set this up are included in Documentation/privileged_db/priv_db_HOWTO
 *
 * The trade off for this additional security is extra complexity in configuration and
 * maintenance of the database, hence it is not enabled at install time and must be
 * done manually.
 *
 * Copyright (C) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @link    https://www.open-emr.org
 */


define("PRIV_DB", "PRIV_DB");
function getPrivDB()
{
    if (!isset($GLOBALS[PRIV_DB])) {
        $secure_config=$GLOBALS['OE_SITE_DIR'] . "/secure_sqlconf.php";
        if (file_exists($secure_config)) {
            require_once($secure_config);
            $GLOBALS[PRIV_DB]=NewADOConnection("mysqli_log"); // Use the subclassed driver which logs execute events
            // Below optionFlags flag is telling the mysql connection to ensure local_infile setting,
            // which is needed to import data in the Administration->Other->External Data Loads feature.
            // (Note the MYSQLI_READ_DEFAULT_GROUP is just to keep the current setting hard-coded in adodb)
            $GLOBALS[PRIV_DB]->optionFlags = array(array(MYSQLI_READ_DEFAULT_GROUP,0), array(MYSQLI_OPT_LOCAL_INFILE,1));
            // Set mysql to use ssl, if applicable.
            // Can support basic encryption by including just the mysql-ca pem (this is mandatory for ssl)
            // Can also support client based certificate if also include mysql-cert and mysql-key (this is optional for ssl)
            if (file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-ca")) {
                if (defined('MYSQLI_CLIENT_SSL')) {
                    $GLOBALS[PRIV_DB]->clientFlags = MYSQLI_CLIENT_SSL;
                }
            }
            $GLOBALS[PRIV_DB]->port = $port;
            $GLOBALS[PRIV_DB]->PConnect($secure_host, $secure_login, $secure_pass, $secure_dbase);
            // set up associations in adodb calls
            $GLOBALS[PRIV_DB]->SetFetchMode(ADODB_FETCH_ASSOC);
            // debug hook for ssl stuff
            if ($GLOBALS['debug_ssl_mysql_connection']) {
                error_log("CHECK SSL CIPHER IN PRIV_DB ADODB: " . errorLogEscape(print_r($GLOBALS[PRIV_DB]->ExecuteNoLog("SHOW STATUS LIKE 'Ssl_cipher';")->fields), true));
            }
        } else {
            $GLOBALS[PRIV_DB]=$GLOBALS['adodb']['db'];
        }
    }

    return $GLOBALS[PRIV_DB];
}

/**
 * mechanism to use "super user" for SQL queries related to password operations
 *
 * @param type $sql
 * @param type $params
 * @return type
 */
function privStatement($sql, $params = null)
{
    if (is_array($params)) {
        $recordset = getPrivDB()->Execute($sql, $params);
    } else {
        $recordset = getPrivDB()->Execute($sql);
    }

    if ($recordset === false) {
      // These error messages are explictly NOT run through xl() because we still
      // need them if there is a database problem.
        echo "Failure during database access! Check server error log.";
        $backtrace=debug_backtrace();

        error_log("Executing as user:" . errorLogEscape(getPrivDB()->user) . " Statement failed:" . errorLogEscape($sql) . ":" . errorLogEscape($GLOBALS['last_mysql_error'])
              . "==>" . errorLogEscape($backtrace[1]["file"]) . " at " . errorLogEscape($backtrace[1]["line"]) . ":" . errorLogEscape($backtrace[1]["function"]));
        exit;
    }

    return $recordset;
    return sqlStatement($sql, $params);
}

/**
 *
 * Wrapper for privStatement that just returns the first row of a query or FALSE
 * if there were no results.
 *
 * @param type $sql
 * @param type $params
 * @return boolean
 */
function privQuery($sql, $params = null)
{
    $recordset=privStatement($sql, $params);
    if ($recordset->EOF) {
        return false;
    }

    $rez = $recordset->FetchRow();
    if ($rez == false) {
        return false;
    }

    return $rez;
}
