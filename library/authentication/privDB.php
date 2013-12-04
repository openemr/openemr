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
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @link    http://www.open-emr.org
 */


define("PRIV_DB","PRIV_DB");
function getPrivDB()
{
    if(!isset($GLOBALS[PRIV_DB]))
    {
        $secure_config=$GLOBALS['OE_SITE_DIR'] . "/secure_sqlconf.php";
        if(file_exists($secure_config))
        {
            require_once($secure_config);
            $GLOBALS[PRIV_DB]=NewADOConnection("mysql_log");
            $GLOBALS[PRIV_DB]->PConnect($secure_host.":".$secure_port, $secure_login, $secure_pass, $secure_dbase);    
        }
        else
        {
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
function privStatement($sql,$params=null)
{
    if(is_array($params))
    {
        $recordset = getPrivDB()->Execute( $sql, $params );   
    }
    else
    {
        $recordset = getPrivDB()->Execute( $sql );   
    }
    if ($recordset === FALSE) {
        
      // These error messages are explictly NOT run through xl() because we still
      // need them if there is a database problem.
      echo "Failure during database access! Check server error log.";
      $backtrace=debug_backtrace();

      error_log("Executing as user:" .getPrivDB()->user." Statement failed:".$sql.":". $GLOBALS['last_mysql_error']
              ."==>".$backtrace[1]["file"]." at ".$backtrace[1]["line"].":".$backtrace[1]["function"]);
      exit;
    }
    return $recordset;
    return sqlStatement($sql,$params);
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
function privQuery($sql,$params=null)
{
    $recordset=privStatement($sql,$params);
    if ($recordset->EOF)
    return FALSE;
    $rez = $recordset->FetchRow();
    if ($rez == FALSE)
        return FALSE;
    return $rez;

}
?>