<?php

// $Id$
// ----------------------------------------------------------------------
// PostNuke Content Management System
// Copyright (C) 2001 by the Post-Nuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: Jim McDonald
// Purpose of file: The PostNuke API
// ----------------------------------------------------------------------

/*
 *
 * Defines
 *
 */

/*        Allows Postnuke to work with register_globals set to off
 *        Patch for php 4.2.x or greater
 */

if (ini_get('register_globals') != 1) {
    $supers = array('_REQUEST',
                            '_ENV',
                            '_SERVER',
                            '_POST',
                            '_GET',
                            '_COOKIE',
                            '_SESSION',
                            '_FILES',
                            '_GLOBALS' );

    foreach ($supers as $__s) {
        if ((isset($$__s) == true) && (is_array($$__s) == true)) {
            extract($$__s, EXTR_OVERWRITE);
        }
    }

    unset($supers);
}


/*
 * State of modules
 */
define('_PNMODULE_STATE_UNINITIALISED', 1);
define('_PNMODULE_STATE_INACTIVE', 2);
define('_PNMODULE_STATE_ACTIVE', 3);
define('_PNMODULE_STATE_MISSING', 4);
define('_PNMODULE_STATE_UPGRADED', 5);

/*
 * Core version informations - should be upgraded on each release for
 * better control on config settings
 */
define('_PN_VERSION_NUM', "0.7.2.6-Phoenix");
define('_PN_VERSION_ID', "PostNuke");
define('_PN_VERSION_SUB', "Phoenix");

/*
 * Fake module for config vars
 */
define('_PN_CONFIG_MODULE', '/PNConfig');

/*
 *
 * Functions
 *
 */

/**
 * get all configuration variable into $pnconfig
 * will be removed on .8
 * @param none
 * @returns true|false
 * @return none
 */
function pnConfigInit()
{
    global $pnconfig;

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $table = $pntable['module_vars'];
    $columns = &$pntable['module_vars_column'];

    /*
     * Make query and go
     */
    $query = "SELECT $columns[name],
                     $columns[value]
              FROM $table
              WHERE $columns[modname]='" . pnVarPrepForStore(_PN_CONFIG_MODULE) . "'";
    $dbresult = $dbconn->Execute($query);
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }

    if ($dbresult->EOF) {
        $dbresult->Close();
        return false;
    }

    while (!$dbresult->EOF) {
        list($k, $v) = $dbresult->fields;
        $dbresult->MoveNext();
        if (
            ($k != 'dbtype') && ($k != 'dbhost') && ($k != 'dbuname') && ($k != 'dbpass')
                && ($k != 'dbname') && ($k != 'system') && ($k != 'prefix') && ($k != 'encoded')
        ) {
            $pnconfig[$k] = $v;
        }
    }

    $dbresult->Close();
    return true;
}

/**
 * get a configuration variable
 * @param name the name of the variable
 * @returns data
 * @return value of the variable, or false on failure
 */
function pnConfigGetVar($name)
{
    global $pnconfig;
    if (isset($pnconfig[$name])) {
        $result = $pnconfig[$name];
    } else {
        /*
         * Fetch base data
         */
        list($dbconn) = pnDBGetConn();
        $pntable = pnDBGetTables();

        $table = $pntable['module_vars'];
        $columns = &$pntable['module_vars_column'];

        /*
         * Make query and go
         */
        $query = "SELECT $columns[value]
                  FROM $table
                  WHERE $columns[modname]='" . pnVarPrepForStore(_PN_CONFIG_MODULE) . "'
                    AND $columns[name]='" . pnVarPrepForStore($name) . "'";
        $dbresult = $dbconn->Execute($query);

        /*
         * In any case of error return false
         */
        if ($dbconn->ErrorNo() != 0) {
            return false;
        }

        if ($dbresult->EOF) {
            $dbresult->Close();
            return false;
        }

        /*
         * Get data
         */
        list ($result) = $dbresult->fields;
        $result = unserialize($result, ['allowed_classes' => false]);

        /*
         * Some caching
         */
        $pnconfig[$name] = $result;

        /*
         * That's all folks
         */
        $dbresult->Close();
    }

    return $result;
}


/**
 * Initialise PostNuke
 * <br />
 * Carries out a number of initialisation tasks to get PostNuke up and
 * running.
 * @returns void
 */
function pnInit()
{
    // Hack for some weird PHP systems that should have the
    // LC_* constants defined, but don't
    if (!defined('LC_TIME')) {
        define('LC_TIME', 'LC_TIME');
    }

    // ADODB configuration
    if (!defined('ADODB_DIR')) {
        define('ADODB_DIR', dirname(__FILE__) . '/../../../../vendor/adodb/adodb-php');
    }

    require_once ADODB_DIR . '/adodb.inc.php';

    // Initialise and load configuration
    global $pnconfig;
    $pnconfig = array();
    require 'config.php';

    // Initialise and load pntables
    global $pntable;
    $pntable = array();
    require 'pntables.php';

    // Connect to database
    if (!pnDBInit()) {
        die('Database initialisation failed');
    }

    // Build up old config array
    pnConfigInit();

    // Other other includes
    require 'includes/pnHTML.php';
    require 'includes/pnMod.php';

    return true;
}

function pnDBInit()
{
    // Get database parameters
    global $pnconfig;
    $dbtype = $pnconfig['dbtype'];
    $dbhost = $pnconfig['dbhost'];
    $dbport = $pnconfig['dbport'];
    $dbname = $pnconfig['dbname'];
    $dbuname = $pnconfig['dbuname'];
    $dbpass = $pnconfig['dbpass'];

    // Database connection is a global (for now)
    global $dbconn;

    // Start connection
    $dbconn = ADONewConnection($dbtype);
    // Set mysql to use ssl, if applicable.
    // Can support basic encryption by including just the mysql-ca pem (this is mandatory for ssl)
    // Can also support client based certificate if also include mysql-cert and mysql-key (this is optional for ssl)
    if (file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-ca")) {
        if (defined('MYSQLI_CLIENT_SSL')) {
            if (
                file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-key") &&
                file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-cert")
            ) {
                // with client side certificate/key
                $dbconn->ssl_key = "${GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-key";
                $dbconn->ssl_cert = "${GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-cert";
                $dbconn->ssl_ca = "${GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-ca";
            } else {
                // without client side certificate/key
                $dbconn->ssl_ca = "${GLOBALS['OE_SITE_DIR']}/documents/certificates/mysql-ca";
            }
            $dbconn->clientFlags = MYSQLI_CLIENT_SSL;
        }
    }
    $dbconn->port = $dbport;

    if ((!empty($GLOBALS["enable_database_connection_pooling"]) || !empty($_SESSION["enable_database_connection_pooling"])) && empty($GLOBALS['connection_pooling_off'])) {
        $dbh = $dbconn->PConnect($dbhost, $dbuname, $dbpass, $dbname);
    } else {
        $dbh = $dbconn->connect($dbhost, $dbuname, $dbpass, $dbname);
    }
    if (!$dbh) {
        //$dbpass = "";
        //die("$dbtype://$dbuname:$dbpass@$dbhost/$dbname failed to connect" . $dbconn->ErrorMsg());
        die("<!DOCTYPE html>\n<html>\n<head>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-1\">\n<title>PostNuke powered Website</title>\n</head>\n<body>\n<center>\n<h1>Problem in Database Connection</h1>\n<br /><br />\n<h5>This Website is powered by PostNuke</h5>\n<a href=\"http://www.postnuke.com\" rel=\"noopener\" target=\"_blank\"><img src=\"images/powered/postnuke.butn.gif\" border=\"0\" alt=\"Web site powered by PostNuke\" hspace=\"10\" /></a> <a href=\"https://php.weblogs.com/ADODB\" rel=\"noopener\" target=\"_blank\"><img src=\"images/powered/adodb2.gif\" alt=\"ADODB database library\" border=\"0\" hspace=\"10\" /></a><a href=\"https://www.php.net\" rel=\"noopener\" target=\"_blank\"><img src=\"images/powered/php2.gif\" alt=\"PHP Scripting Language\" border=\"0\" hspace=\"10\" /></a><br />\n<h5>Although this site is running the PostNuke software<br />it has no other connection to the PostNuke Developers.<br />Please refrain from sending messages about this site or its content<br />to the PostNuke team, the end will result in an ignored e-mail.</h5>\n</center>\n</body>\n</html>");
    }

    // Modified 5/2009 by BM for UTF-8 project
    if ($pnconfig['db_encoding'] == "utf8mb4") {
        $success_flag = $dbconn->Execute("SET NAMES 'utf8mb4'");
        if (!$success_flag) {
            error_log("PHP custom error: from postnuke interface/main/calendar/includes/pnAPI.php - Unable to set up UTF8MB4 encoding with mysql database", 0);
        }
    } elseif ($pnconfig['db_encoding'] == "utf8") {
        $success_flag = $dbconn->Execute("SET NAMES 'utf8'");
        if (!$success_flag) {
            error_log("PHP custom error: from postnuke interface/main/calendar/includes/pnAPI.php - Unable to set up UTF8 encoding with mysql database", 0);
        }
    }

    // ---------------------------------------

    //Turn off STRICT SQL
    $sql_strict_set_success = $dbconn->Execute("SET sql_mode = ''");
    if (!$sql_strict_set_success) {
        error_log("PHP custom error: from postnuke interface/main/calendar/includes/pnAPI.php - Unable to set strict sql setting", 0);
    }

    global $ADODB_FETCH_MODE;
    $ADODB_FETCH_MODE = ADODB_FETCH_NUM;

    // force oracle to a consistent date format for comparison methods later on
    if (strcmp($dbtype, 'oci8') == 0) {
        $dbconn->Execute("alter session set NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
    }

    // Sync MySQL time zone with PHP time zone.
    $dbconn->Execute("SET time_zone = '" . add_escape_custom((new DateTime())->format("P")) . "'");

    if (!empty($GLOBALS['debug_ssl_mysql_connection'])) {
        error_log("CHECK SSL CIPHER IN CALENDAR ADODB: " . errorLogEscape(print_r($dbconn->Execute("SHOW STATUS LIKE 'Ssl_cipher';")->fields, true)));
    }

    return true;
}

/**
 * get a list of database connections
 * @returns array
 * @return array of database connections
 */
function pnDBGetConn()
{
    global $dbconn;

    return array($dbconn);
}

/**
 * get a list of database tables
 * @returns array
 * @return array of database tables
 */
function pnDBGetTables()
{
    global $pntable;

    return $pntable;
}

/**
 * clean user input
 * <br />
 * Gets a global variable, cleaning it up to try to ensure that
 * hack attacks don't work
 * @param var name of variable to get
 * @param ...
 * @returns string/array
 * @return prepared variable if only one variable passed
 * in, otherwise an array of prepared variables
 */
function pnVarCleanFromInput()
{
    $search = array('|</?\s*SCRIPT.*?>|si',
                    '|</?\s*FRAME.*?>|si',
                    '|</?\s*OBJECT.*?>|si',
                    '|</?\s*META.*?>|si',
                    '|</?\s*APPLET.*?>|si',
                    '|</?\s*LINK.*?>|si',
                    '|</?\s*IFRAME.*?>|si',
                    '|STYLE\s*=\s*"[^"]*"|si');

    $replace = array('');

    $resarray = array();
    foreach (func_get_args() as $var) {
    // Get var
        global $$var;
        if (empty($var)) {
            return;
        }

        $ourvar = $$var;
        if (!isset($ourvar)) {
            array_push($resarray, null);
            continue;
        }

        if (empty($ourvar)) {
            array_push($resarray, $ourvar);
            continue;
        }

        // Add to result array
        array_push($resarray, $ourvar);
    }

    // Return vars
    if (func_num_args() == 1) {
        return $resarray[0];
    } else {
        return $resarray;
    }
}

/**
 * ready user output
 * <br />
 * Gets a variable, cleaning it up such that the text is
 * shown exactly as expected
 * @param var variable to prepare
 * @param ...
 * @returns string/array
 * @return prepared variable if only one variable passed
 * in, otherwise an array of prepared variables
 */
function pnVarPrepForDisplay()
{
    // This search and replace finds the text 'x@y' and replaces
    // it with HTML entities, this provides protection against
    // email harvesters
    static $search = array('/(.)@(.)/s');

    $resarray = array();

    foreach (func_get_args() as $ourvar) {
        // Prepare var
        $ourvar = htmlspecialchars($ourvar);

        $ourvar = preg_replace_callback(
            $search,
            function ($m) {

                $output = "";
                for ($i = 0; $i < (strlen($m[0])); $i++) {
                    $output .= '&#' . ord($m[0][$i]) . ';';
                }

                return $output;
            },
            $ourvar
        );


        // Add to array
        array_push($resarray, $ourvar);
    }

    // Return vars
    if (func_num_args() == 1) {
        return $resarray[0];
    } else {
        return $resarray;
    }
}

/**
 * ready HTML output
 * <br />
 * Gets a variable, cleaning it up such that the text is
 * shown exactly as expected, except for allowed HTML tags which
 * are allowed through
 * @param var variable to prepare
 * @param ...
 * @returns string/array
 * @return prepared variable if only one variable passed
 * in, otherwise an array of prepared variables
 */
function pnVarPrepHTMLDisplay()
{
    // This search and replace finds the text 'x@y' and replaces
    // it with HTML entities, this provides protection against
    // email harvesters
    //
    // Note that the use of \024 and \022 are needed to ensure that
    // this does not break HTML tags that might be around either
    // the username or the domain name
    static $search = array('/([^\024])@([^\022])/s');

    static $allowedhtml;

    if (!isset($allowedhtml)) {
        $allowedhtml = array();
    }

    $resarray = array();
    foreach (func_get_args() as $ourvar) {
        // Preparse var to mark the HTML that we want
        $ourvar = preg_replace($allowedhtml, "\022\\1\024", $ourvar);

        // Prepare var
        $ourvar = htmlspecialchars($ourvar);
        $ourvar = preg_replace_callback(
            $search,
            function ($matches) {
                return "&#" .
                sprintf("%03d", ord($matches[1])) .
                ";&#064;&#" .
                sprintf("%03d", ord($matches[2])) . ";";
            },
            $ourvar
        );

        // Fix the HTML that we want
        $ourvar = preg_replace_callback(
            '/\022([^\024]*)\024/',
            function ($matches) {
                return '<' . strtr("$matches[1]", array('&gt;' => '>', '&lt;' => '<', '&quot;' => '\"')) . '>';
            },
            $ourvar
        );

        // Fix entities if required
        if (pnConfigGetVar('htmlentities')) {
            $ourvar = preg_replace('/&amp;([a-z#0-9]+);/i', "&\\1;", $ourvar);
        }

        // Add to array
        array_push($resarray, $ourvar);
    }

    // Return vars
    if (func_num_args() == 1) {
        return $resarray[0];
    } else {
        return $resarray;
    }
}

/**
 * ready databse output
 * <br />
 * Gets a variable, cleaning it up such that the text is
 * stored in a database exactly as expected
 * @param var variable to prepare
 * @param ...
 * @returns string/array
 * @return prepared variable if only one variable passed
 * in, otherwise an array of prepared variables
 */
function pnVarPrepForStore()
{
    $resarray = array();
    foreach (func_get_args() as $ourvar) {
        // Prepare var
        $ourvar = add_escape_custom($ourvar);

        // Add to array
        array_push($resarray, $ourvar);
    }

    // Return vars
    if (func_num_args() == 1) {
        return $resarray[0];
    } else {
        return $resarray;
    }
}

/**
 * ready operating system output
 * <br />
 * Gets a variable, cleaning it up such that any attempts
 * to access files outside of the scope of the PostNuke
 * system is not allowed
 * @param var variable to prepare
 * @param ...
 * @returns string/array
 * @return prepared variable if only one variable passed
 * in, otherwise an array of prepared variables
 */
function pnVarPrepForOS()
{
    static $search = array('!\.\./!si', // .. (directory traversal)
                           '!^.*://!si', // .*:// (start of URL)
                           '!/!si',     // Forward slash (directory traversal)
                           '!\\\\!si'); // Backslash (directory traversal)

    static $replace = array('',
                            '',
                            '_',
                            '_');

    $resarray = array();
    foreach (func_get_args() as $ourvar) {
        // Parse out bad things
        $ourvar = preg_replace($search, $replace, $ourvar);

        // Prepare var
        $ourvar = addslashes($ourvar);

        // Add to array
        array_push($resarray, $ourvar);
    }

    // Return vars
    if (func_num_args() == 1) {
        return $resarray[0];
    } else {
        return $resarray;
    }
}


/**
 * get base URI for PostNuke
 * @returns string
 * @return base URI for PostNuke
 */
function pnGetBaseURI()
{
    global $HTTP_SERVER_VARS;

    // Get the name of this URI

    // Start of with REQUEST_URI
    if (isset($HTTP_SERVER_VARS['REQUEST_URI'])) {
        $path = $HTTP_SERVER_VARS['REQUEST_URI'];
    } else {
        $path = getenv('REQUEST_URI');
    }

    if (
        (empty($path)) ||
        (substr($path, -1, 1) == '/')
    ) {
        // REQUEST_URI was empty or pointed to a path
        // Try looking at PATH_INFO
        $path = getenv('PATH_INFO');
        if (empty($path)) {
            // No luck there either
            // Try SCRIPT_NAME
            if (isset($HTTP_SERVER_VARS['SCRIPT_NAME'])) {
                $path = $HTTP_SERVER_VARS['SCRIPT_NAME'];
            } else {
                $path = getenv('SCRIPT_NAME');
            }
        }
    }

    $path = preg_replace('/[#\?].*/', '', $path);
    $path = dirname($path);

    if (preg_match('!^[/\\\]*$!', $path)) {
        $path = '';
    }

    return $path;
}

/**
 * get base URL for PostNuke
 * @returns string
 * @return base URL for PostNuke
 */
function pnGetBaseURL()
{

    // Removed majority of this function in 10/2017 to just use relative path
    // (the full path would break in some https server setups)

    $path = pnGetBaseURI();

    return "$path/";
}
