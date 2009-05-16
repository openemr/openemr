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

if (phpversion() >= "4.2.0") {
	if ( ini_get('register_globals') != 1 ) {
		$supers = array('_REQUEST',
                                '_ENV',
                                '_SERVER',
                                '_POST',
                                '_GET',
                                '_COOKIE',
                                '_SESSION',
                                '_FILES',
                                '_GLOBALS' );

		foreach( $supers as $__s) {
			if ( (isset($$__s) == true) && (is_array( $$__s ) == true) ) extract( $$__s, EXTR_OVERWRITE );
		}
		unset($supers);
	}
} else {
	if ( ini_get('register_globals') != 1 ) {

		$supers = array('HTTP_POST_VARS',
                                'HTTP_GET_VARS',
                                'HTTP_COOKIE_VARS',
                                'GLOBALS',
                                'HTTP_SESSION_VARS',
                                'HTTP_SERVER_VARS',
                                'HTTP_ENV_VARS'
                                 );

		foreach( $supers as $__s) {
			if ( (isset($$__s) == true) && (is_array( $$__s ) == true) ) extract( $$__s, EXTR_OVERWRITE );
		}
		unset($supers);
	}
}

/*
 * Yes/no integer
 */
define('_PNYES', 1);
define('_PNNO', 0);

/*
 * State of modules
 */
define('_PNMODULE_STATE_UNINITIALISED', 1);
define('_PNMODULE_STATE_INACTIVE', 2);
define('_PNMODULE_STATE_ACTIVE', 3);
define('_PNMODULE_STATE_MISSING', 4);
define('_PNMODULE_STATE_UPGRADED', 5);

/*
 * 'All' and 'unregistered' for user and group permissions
 */
define('_PNPERMS_ALL', '-1');
define('_PNPERMS_UNREGISTERED', '0');

/*
 * Core version informations - should be upgraded on each release for
 * better control on config settings
 */
define('_PN_VERSION_NUM',       "0.7.2.6-Phoenix");
define('_PN_VERSION_ID',        "PostNuke");
define('_PN_VERSION_SUB',       "Phoenix");

/*
 * Fake module for config vars
 */
define('_PN_CONFIG_MODULE',     '/PNConfig');

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
function pnConfigInit() {
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
    if($dbconn->ErrorNo() != 0) {
        return false;
    }
    if ($dbresult->EOF) {
        $dbresult->Close();
        return false;
    }
    while(!$dbresult->EOF) {
        list($k, $v) = $dbresult->fields;
        $dbresult->MoveNext();
        if (($k != 'dbtype') && ($k != 'dbhost') && ($k != 'dbuname') && ($k != 'dbpass')
                && ($k != 'dbname') && ($k != 'system') && ($k != 'prefix') && ($k != 'encoded')) {
            $v =@unserialize($v);
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
        if($dbconn->ErrorNo() != 0) {
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
        $result = unserialize($result);

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
 * set a configuration variable
 * @param name the name of the variable
 * @param value the value of the variable
 * @returns bool
 * @return true on success, false on failure
 */
function pnConfigSetVar($name, $value)
{
    /*
     * The database parameter are not allowed to change
     */
    if (empty($name) || ($name == 'dbtype') || ($name == 'dbhost') || ($name == 'dbuname') || ($name == 'dbpass')
            || ($name == 'dbname') || ($name == 'system') || ($name == 'prefix') || ($name == 'encoded')) {
        return false;
    }

    /*
     * Test on missing record
     *
     * Also solve SF-bug #580951
     */
    $must_insert = true;
    global $pnconfig;
    foreach($pnconfig as $k => $v) {
        /*
         * Test if the key name is in the array
         */
        if ($k == $name) {
            /*
             * Set flag
             */
            $must_insert = false;
            /*
             * Test on change. If not, just quit now
             */
            if ($v == $value) {
                return true;
            }
            /*
             * End loop after success
             */
            break;
        }
    }

    /*
     * Fetch base data
     */
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    $table = $pntable['module_vars'];
    $columns = &$pntable['module_vars_column'];

    /*
     * Update the table
     */
    if ($must_insert) {
        /*
         * Insert
         */
        $query = "INSERT INTO $table
                  ($columns[modname],
                   $columns[name],
                   $columns[value])
                  VALUES ('" . pnVarPrepForStore(_PN_CONFIG_MODULE) . "',
                          '" . pnVarPrepForStore($name) . "',
                          '" . pnVarPrepForStore(serialize($value)). "')";
    } else {
        /*
         * Update
         */
         $query = "UPDATE $table
                   SET $columns[value]='" . pnVarPrepForStore(serialize($value)) . "'
                   WHERE $columns[modname]='" . pnVarPrepForStore(_PN_CONFIG_MODULE) . "'
                   AND $columns[name]='" . pnVarPrepForStore($name) . "'";
    }
    $dbconn->Execute($query);
    if($dbconn->ErrorNo() != 0) {
        return false;
    }

    /*
     * Update my vars
     */
    $pnconfig[$name] = $value;

    return true;
}


/**
 * delete a configuration variable
 * @param name the name of the variable
 * @returns bool
 * @return true on success, false on failure
 */
function pnConfigDelVar($name)
{
    global $pnconfig;

    if (empty($name)) {
        return false;
    }

    // Don't allow deleting at current
    return false;
}

/**
 * Initialise PostNuke
 * <br>
 * Carries out a number of initialisation tasks to get PostNuke up and
 * running.
 * @returns void
 */
function pnInit()
{
    // proper error_repoting
    // e_all for development
    // error_reporting(E_ALL);
    // without warnings and notices for release
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

    // Hack for some weird PHP systems that should have the
    // LC_* constants defined, but don't
    if (!defined('LC_TIME')) {
        define('LC_TIME', 'LC_TIME');
    }

    // ADODB configuration
    define('ADODB_DIR', 'pnadodb');
    require 'pnadodb/adodb.inc.php';

    // Temporary fix for hacking the hlpfile global
    // TODO - remove with pre-0.71 code
    global $hlpfile;
    $hlpfile = '';

    // Initialise and load configuration
    global $pnconfig, $pndebug;
    $pnconfig = array();
    include 'config.php';


    // Set up multisites
    // added this @define for .71, ugly ?
    // i guess the E_ALL stuff.
    @define('WHERE_IS_PERSO', '');

    // Initialise and load pntables
    global $pntable;
    $pntable = array();
    // if a multisite has its own pntables.
    if (file_exists(WHERE_IS_PERSO.'pntables.php')) {
        include WHERE_IS_PERSO.'pntables.php';
    } else {
        require 'pntables.php';
    }

    // Decode encoded DB parameters
    if ($pnconfig['encoded']) {
        $pnconfig['dbuname'] = base64_decode($pnconfig['dbuname']);
        $pnconfig['dbpass'] = base64_decode($pnconfig['dbpass']);
        $pnconfig['encoded'] = 0;
    }
    // Connect to database
    if (!pnDBInit()) {
        die('Database initialisation failed');
    }

    // debugger if required
    if ($pndebug['debug']){
        include_once 'includes/lensdebug.inc.php';
        global $dbg, $debug_sqlcalls;
        $dbg = new LensDebug();
        $debug_sqlcalls = 0;
    }

    // Build up old config array
    pnConfigInit();

    // Set compression on if desired
    //
    if (pnConfigGetVar('UseCompression') == 1) {
    ob_start("ob_gzhandler");
    }

    // Other includes
    include 'includes/pnSession.php';
    include 'includes/pnUser.php';

    // Start session
    if (!pnSessionSetup()) {
        die('Session setup failed');
    }

	if (!pnSessionInit()) {
        die('Session initialisation failed');
    }

    include 'includes/security.php';

    // See if a language update is required
    $newlang = pnVarCleanFromInput('newlang');
    if (!empty($newlang)) {
        $lang = $newlang;
        pnSessionSetVar('lang', $newlang);
    } else {
        $lang = pnSessionGetVar('lang');
    }

    // Load global language defines
    if (isset ($lang) && file_exists('language/' . pnVarPrepForOS($lang) . '/global.php')) {
        $currentlang = $lang;
    } else {
        $currentlang = pnConfigGetVar('language');
        pnSessionSetVar('lang', $currentlang);
    }
    include 'language/' . pnVarPrepForOS($currentlang) . '/global.php';

    include 'modules/NS-Languages/api.php';

        // Cross-Site Scripting attack defense - Sent by larsneo
        // some syntax checking against injected javascript

        $pnAntiCrackerMode = pnConfigGetVar('pnAntiCracker');

        if ( $pnAntiCrackerMode == 1 ) {
                pnSecureInput();
        }
    // Banner system
    include 'includes/pnBanners.php';

    // Other other includes
    include 'includes/advblocks.php';
    include 'includes/counter.php';
    include 'includes/pnHTML.php';
    include 'includes/pnMod.php';
    include 'includes/queryutil.php';
    include 'includes/xhtml.php';
    include 'includes/oldfuncs.php';

    // Handle referer
    if (pnConfigGetVar('httpref') == 1) {
        include 'referer.php';
        httpreferer();
    }

    return true;
}

function pninclude_once($file)
{
    include_once($file);
}

function pnDBInit()
{
    // Get database parameters
    global $pnconfig;
    $dbtype = $pnconfig['dbtype'];
    $dbhost = $pnconfig['dbhost'];
    $dbname = $pnconfig['dbname'];
    $dbuname = $pnconfig['dbuname'];
    $dbpass = $pnconfig['dbpass'];

    // Database connection is a global (for now)
    global $dbconn;

    // Start connection
    $dbconn = ADONewConnection($dbtype);
    $dbh = $dbconn->Connect($dbhost, $dbuname, $dbpass, $dbname);
    if (!$dbh) {
    	//$dbpass = "";
        //die("$dbtype://$dbuname:$dbpass@$dbhost/$dbname failed to connect" . $dbconn->ErrorMsg());
		die("<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n<html>\n<head>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-1\">\n<title>PostNuke powered Website</title>\n</head>\n<body>\n<center>\n<h1>Problem in Database Connection</h1>\n<br /><br />\n<h5>This Website is powered by PostNuke</h5>\n<a href=\"http://www.postnuke.com\" target=\"_blank\"><img src=\"images/powered/postnuke.butn.gif\" border=\"0\" alt=\"Web site powered by PostNuke\" hspace=\"10\" /></a> <a href=\"http://php.weblogs.com/ADODB\" target=\"_blank\"><img src=\"images/powered/adodb2.gif\" alt=\"ADODB database library\" border=\"0\" hspace=\"10\" /></a><a href=\"http://www.php.net\" target=\"_blank\"><img src=\"images/powered/php2.gif\" alt=\"PHP Scripting Language\" border=\"0\" hspace=\"10\" /></a><br />\n<h5>Although this site is running the PostNuke software<br />it has no other connection to the PostNuke Developers.<br />Please refrain from sending messages about this site or its content<br />to the PostNuke team, the end will result in an ignored e-mail.</h5>\n</center>\n</body>\n</html>");
    }
    
    // Modified 5/2009 by BM for UTF-8 project
    if ($pnconfig['utf8Flag']) {
        $success_flag = $dbconn->Execute("SET NAMES 'utf8'");
        if (!$success_flag) {
            error_log("PHP custom error: from postnuke interface/main/calendar/includes/pnAPI.php - Unable to set up UTF8 encoding with mysql database", 0);
        }
    }
    // ---------------------------------------
    
    global $ADODB_FETCH_MODE;
    $ADODB_FETCH_MODE = ADODB_FETCH_NUM;

    // force oracle to a consistent date format for comparison methods later on
    if (strcmp($dbtype, 'oci8') == 0) {
        $dbconn->Execute("alter session set NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
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
 * <br>
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
            array_push($resarray, NULL);
            continue;
        }
        if (empty($ourvar)) {
            array_push($resarray, $ourvar);
            continue;
        }

        // Clean var
        if (get_magic_quotes_gpc()) {
            pnStripslashes($ourvar);
        }
        if (!pnSecAuthAction(0, '::', '::', ACCESS_ADMIN)) {
            $ourvar = preg_replace($search, $replace, $ourvar);
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
 * strip slashes
 *
 * stripslashes on multidimensional arrays.
 * Used in conjunction with pnVarCleanFromInput
 * @access private
 * @param any variables or arrays to be stripslashed
 */
function pnStripslashes (&$value) {
    if(!is_array($value)) {
        $value = stripslashes($value);
    } else {
        array_walk($value,'pnStripslashes');
    }
}

/**
 * ready user output
 * <br>
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
    static $search = array('/(.)@(.)/se');

    static $replace = array('"&#" .
                            sprintf("%03d", ord("\\1")) .
                            ";&#064;&#" .
                            sprintf("%03d", ord("\\2")) . ";";');

    $resarray = array();
    foreach (func_get_args() as $ourvar) {

        // Prepare var
        $ourvar = htmlspecialchars($ourvar);

        $ourvar = preg_replace($search, $replace, $ourvar);

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
 * <br>
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
    static $search = array('/([^\024])@([^\022])/se');

    static $replace = array('"&#" .
                            sprintf("%03d", ord("\\1")) .
                            ";&#064;&#" .
                            sprintf("%03d", ord("\\2")) . ";";');

    static $allowedhtml;

    if (!isset($allowedhtml)) {
        $allowedhtml = array();
        foreach(pnConfigGetVar('AllowableHTML') as $k=>$v) {
            switch($v) {
                case 0:
                    break;
                case 1:
                    $allowedhtml[] = "|<(/?$k)\s*/?>|i";
                    break;
                case 2:
                    $allowedhtml[] = "|<(/?$k(\s+.*?)?)>|i";
                    break;
            }
        }
    }

    $resarray = array();
    foreach (func_get_args() as $ourvar) {
        // Preparse var to mark the HTML that we want
        $ourvar = preg_replace($allowedhtml, "\022\\1\024", $ourvar);

        // Prepare var
        $ourvar = htmlspecialchars($ourvar);
        $ourvar = preg_replace($search, $replace, $ourvar);

        // Fix the HTML that we want
        $ourvar = preg_replace('/\022([^\024]*)\024/e',
                               "'<' . strtr('\\1', array('&gt;' => '>',
                                                         '&lt;' => '<',
                                                         '&quot;' => '\"'))
                               . '>';", $ourvar);

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
 * <br>
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
        if (!get_magic_quotes_runtime()) {
            $ourvar = addslashes($ourvar);
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
 * ready operating system output
 * <br>
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
        if (!get_magic_quotes_runtime()) {
            $ourvar = addslashes($ourvar);
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
 * remove censored words
 */
function pnVarCensor()
{
    static $docensor;
    if (!isset($docensor)) {
        $docensor = pnConfigGetVar('CensorMode');
    }

    static $search = array();
    if (empty($search)) {
        $repsearch = array('/o/i',
                           '/e/i',
                           '/a/i',
                           '/i/i');
        $repreplace = array('0',
                            '3',
                            '@',
                            '1');
        $censoredwords = pnConfigGetVar('CensorList');
        foreach ($censoredwords as $censoredword) {
            // Simple word
            $search[] = "/\b$censoredword\b/i";

            // Common replacements
            $mungedword = preg_replace($repsearch, $repreplace, $censoredword);
            if ($mungedword != $censoredword) {
                $search[] = "/\b$mungedword\b/";
            }
        }
    }

    $replace = pnConfigGetVar('CensorReplace');

    $resarray = array();
    foreach (func_get_args() as $ourvar) {

        if ($docensor) {
            // Parse out nasty words
            $ourvar = preg_replace($search, $replace, $ourvar);
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
 * validate a user variable
 * @access public
 * @author Damien Bonvillain
 * @author Gregor J. Rothfuss
 * @since 1.23 - 2002/02/01
 * @param var the variable to validate
 * @param type the type of the validation to perform
 * @param args optional array with validation-specific settings
 * @returns bool
 * @return true if the validation was successful, false otherwise
 */
function pnVarValidate($var, $type, $args=0)
{
 switch ($type) {
    case 'email':
        // all characters must be 7 bit ascii
        $length = strlen($var);
        $idx = 0;
        while($length--) {
           $c = $var[$idx++];
           if(ord($c) > 127){
              return false;
           }
        }
        $regexp = '/^(?:[^\s\000-\037\177\(\)<>@,;:\\"\[\]]\.?)+@(?:[^\s\000-\037\177\(\)<>@,;:\\\"\[\]]\.?)+\.[a-z]{2,6}$/Ui';
        if(preg_match($regexp,$var)) {
            return true;
        } else {
            return false;
        }
        break;

    case 'url':
        // all characters must be 7 bit ascii
        $length = strlen($var);
        $idx = 0;
        while($length--) {
           $c = $var[$idx++];
           if(ord($c) > 127){
             return false;
           }
        }
        $regexp = '/^([!\$\046-\073=\077-\132_\141-\172~]|(?:%[a-f0-9]{2}))+$/i';
        if(!preg_match($regexp, $var)) {
            return false;
        }
        $url_array = @parse_url($var);
        if(empty($url_array)) {
            return false;
        } else {
            return !empty($url_array['scheme']);
        }
        break;
   }
}

/**
 * check an assertion
 * <br>
 * Check an assertion to ensure that it is valid.  If not, then die
 * @param assertion the assertion
 * @param filename the filename the assertion occurs in
 * @param line the line number the assertion occurs in
 */
function pnAssert($assertion, $file='Unknown', $line='Unknown', $msg='')
{
    if ($assertion) {
        return;
    }

    // Assertion failed - log it
    if (!empty($msg)) {
        die("Assertion failed in $file at line $line - $msg");
    } else {
        die("Assertion failed in $file at line $line");
    }
}

/**
 * get status message from previous operation
 * <br>
 * Obtains any status message, and also destroys
 * it from the session to prevent duplication
 * @returns string
 * @return the status message
 */
function pnGetStatusMsg()
{
    $msg = pnSessionGetVar('statusmsg');
    pnSessionDelVar('statusmsg');
    $errmsg = pnSessionGetVar('errormsg');
    pnSessionDelVar('errormsg');

    // Error message overrides status message
    if (!empty($errmsg)) {
        return $errmsg;
    }
    return $msg;
}

function pnThemeLoad($thistheme)
{
    static $loaded = 0;

    if ($loaded) {
        return true;
    }

    // Lots of nasty globals for back-compatability with older themes
    global $bgcolor1;
    global $bgcolor2;
    global $bgcolor3;
    global $bgcolor4;
    global $bgcolor5;
    global $sepcolor;
    global $textcolor1;
    global $textcolor2;
    global $postnuke_theme;
    global $thename;

    // modification mouzaia .71

    // is this really useful ?
/*  $themefile = 'themes/' . pnVarPrepForOS(pnUserGetTheme()) . '/theme.php';
    if (!file_exists($themefile)) {
        return false;
    }
*/
// eugenio themeover 20020413
    if (@file(WHERE_IS_PERSO."themes/$thistheme/theme.php"))
        { include WHERE_IS_PERSO."themes/$thistheme/theme.php"; }
    else
        {
        include "themes/$thistheme/theme.php";
        }
    // end of modification
    $loaded = 1;
    return true;
}

function pnThemeGetVar($name)
{
    global $$name;
    if (isset($$name)) {
        return $$name;
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
    if ((empty($path)) ||
        (substr($path, -1, 1) == '/')) {
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
    global $HTTP_SERVER_VARS;

    if (empty($HTTP_SERVER_VARS['HTTP_HOST'])) {
        $server = getenv('HTTP_HOST');
    } else {
        $server = $HTTP_SERVER_VARS['HTTP_HOST'];
    }
    // IIS sets HTTPS=off
    if (isset($HTTP_SERVER_VARS['HTTPS']) && $HTTP_SERVER_VARS['HTTPS'] != 'off') {
        $proto = 'https://';
    } else {
        $proto = 'http://';
    }

    $path = pnGetBaseURI();

    return "$proto$server$path/";
}

/**
 * Carry out a redirect
 * @param the URL to redirect to
 * @returns void
 */
function pnRedirect($redirecturl)
{
    // Always close session before redirect
    if (function_exists('session_write_close')) {
        session_write_close();
    }

    if (preg_match('!^http!', $redirecturl)) {
        // Absolute URL - simple redirect
        Header("Location: $redirecturl");
        return;
    } else {
        // Removing leading slashes from redirect url
        $redirecturl = preg_replace('!^/*!', '', $redirecturl);

        // Get base URL
        $baseurl = pnGetBaseURL();

        Header("Location: $baseurl$redirecturl");
    }

}

/**
 * check to see if this is a local referral
 * @returns bool
 * @return true if locally referred, false if not
 */
function pnLocalReferer()
{
    global $HTTP_SERVER_VARS;

    if (empty($HTTP_SERVER_VARS['HTTP_HOST'])) {
        $server = getenv('HTTP_HOST');
    } else {
        $server = $HTTP_SERVER_VARS['HTTP_HOST'];
    }

    if (empty($HTTP_SERVER_VARS['HTTP_REFERER'])) {
        $referer = getenv('HTTP_REFERER');
    } else {
        $referer = $HTTP_SERVER_VARS['HTTP_REFERER'];
    }

    if (empty($referer) || preg_match("!^http://$server/!", $referer)) {
        return true;
    } else {
        return false;
    }
}

// Hack - we need this for themes, but will get rid of it soon
if (!function_exists('GetUserTime')) {
    function GetUserTime($time) {
        if (pnUserLoggedIn()) {
            $time += (pnUserGetVar('timezone_offset') - pnConfigGetVar('timezone_offset')) * 3600;
        }
        return($time);
    }
}

/**
 * send an email
 * @param to - recipient of the email
 * @param subject - title of the email
 * @param message - body of the email
 * @param headers - extra headers for the email
 * @param debug - if 1, echo mail content
 * @returns bool
 * @return true if the email was sent, false if not
 */
function pnMail($to, $subject, $message, $headers, $debug=0)
{
    // Language translations
    switch(pnUserGetLang()) {
        case 'rus':
        if (!empty($headers)) $headers .= "\n";
            $headers .= "Content-Type: text/plain; charset=koi8-r";
            $subject = convert_cyr_string($subject,"w","k");
            $message = convert_cyr_string($message,"w","k");
            $headers = convert_cyr_string($headers,"w","k");
            break;
    }
    
    // Debug
    if ($debug) {
    	echo "Mail To: ".$to."<br>";
    	echo "Mail Subject: ".$subject."<br>";
    	echo "Mail Message: ".$message."<br>";
    	echo "Mail Headers: ".$headers."<br>";
	}
	
    // Mail message
    // do not display error messages [class007]
    $return = @mail($to, $subject, $message, $headers);
    return $return;
}

/* Protects better diverse attempts of Cross-Site Scripting
   attacks, thanks to webmedic, Timax, larsneo.
 */

function pnSecureInput() {

/*      Lets validate the current php version and set globals
        accordingly.
        Do not change this value unless you know what you are
        doing you have been warned!
 */

//require('includes/htmlfilter.inc');

if ( phpversion() >= "4.2.0" ) {

$HTTP_GET_VARS          = $_GET;
$HTTP_POST_VARS         = $_POST;
$HTTP_COOKIE_VARS       = $_COOKIE;

} else {

global $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS;

}

// Cross-Site Scripting attack defense - Sent by larsneo
// some syntax checking against injected javascript
// extended by Neo

if (count($HTTP_GET_VARS) > 0) {

/*        Lets now sanitize the GET vars
 */


        foreach ($HTTP_GET_VARS as $secvalue) {
        	if (!is_array($secvalue)) {
                if ((eregi("<[^>]*script*\"?[^>]*>", $secvalue)) ||
                        (eregi(".*[[:space:]](or|and)[[:space:]].*(=|like).*", $secvalue)) ||
                        (eregi("<[^>]*object*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*iframe*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*applet*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*meta*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*style*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*form*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*window.*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*alert*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*img*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*document.*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*cookie*\"?[^>]*>", $secvalue)) ||
                        (eregi("\"", $secvalue))) {
                        //pnMailHackAttempt(__FILE__,__LINE__,'pnSecurity Alert','Intrusion detection.');
                        //Header("Location: index.php");
                }
        	}
        }
}

/*        Lets now sanitize the POST vars
 */

if ( count($HTTP_POST_VARS) > 0) {

        foreach ($HTTP_POST_VARS as $secvalue) {
        	if (!is_array($secvalue)) {
                if ((eregi("<[^>]*script*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*object*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*iframe*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*applet*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*window.*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*alert*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*document.*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*cookie*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*meta*\"?[^>]*>", $secvalue))
                        ) {

                        //pnMailHackAttempt(__FILE__,__LINE__,'pnSecurity Alert','Intrusion detection.');
                        //Header("Location: index.php");
                }
         	}
        }

}

/*        Lets now sanitize the COOKIE vars
 */

if ( count($HTTP_COOKIE_VARS) > 0) {

        foreach ($HTTP_COOKIE_VARS as $secvalue) {
			if (!is_array($secvalue)) {
                if ((eregi("<[^>]*script*\"?[^>]*>", $secvalue)) ||
                        (eregi(".*[[:space:]](or|and)[[:space:]].*(=|like).*", $secvalue)) ||
                        (eregi("<[^>]*object*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*iframe*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*applet*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*meta*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*style*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*form*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*window.*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*alert*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*document.*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*cookie*\"?[^>]*>", $secvalue)) ||
                        (eregi("<[^>]*img*\"?[^>]*>", $secvalue))
                        ) {

                        pnMailHackAttempt(__FILE__,__LINE__,'pnSecurity Alert','Intrusion detection.');
                        //Header("Location: index.php");
                }
        	}
        }
}


} # End of secure Input

/*         Function that compares the current php version on the
        system with the target one
 */

// Deprecate function reverting to php detecion function

function pnPhpVersionCheck($vercheck) {

$minver = str_replace(".","", $vercheck);
$curver = str_replace(".","", phpversion());

        if($curver >= $minver){
                return true;
                } else {
                return false;
        }
}

function pnMailHackAttempt( $detecting_file        =        "(no filename available)",
                            $detecting_line        =        "(no line number available)",
                            $hack_type             =        "(no type given)",
                            $message               =        "(no message given)" ) {

# Backwards compatibility fix with php 4.0.x and 4.1.x or greater Neo

if (phpversion() >= "4.2.0") {

		$_pv  = $_POST;
		$_gv  = $_GET;
		$_rv  = $_REQUEST;
		$_sv  = $_SERVER;
		$_ev  = $_ENV;
		$_cv  = $_COOKIE;
		$_fv  = $_FILES;
		$_snv = $_SESSION;
		
	} else {

	global $HTTP_POST_VARS, $HTTP_GET_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS, $HTTP_COOKIE_VARS, $HTTP_POST_FILES, $HTTP_SESSION_VARS;

		$_pv  = $HTTP_POST_VARS;
		$_gv  = $HTTP_GET_VARS;
		$_rv  = array();
		$_sv  = $HTTP_SERVER_VARS;
		$_ev  = $HTTP_ENV_VARS;
		$_cv  = $HTTP_COOKIE_VARS;
		$_fv  = $HTTP_POST_FILES;
		$_snv = $HTTP_SESSION_VARS;

}
        $output         =        "Attention site admin of ".pnConfigGetVar('sitename').",\n";
        $output        .=        "On ".ml_ftime( _DATEBRIEF, ( GetUserTime( time( ) ) ) );
        $output        .=        " at ". ml_ftime( _TIMEBRIEF, ( GetUserTime( time( ) ) ) );
        $output        .=        " the Postnuke code has detected that somebody tried to"
                           ." send information to your site that may have been intended"
                           ." as a hack. Do not panic, it may be harmless: maybe this"
                           ." detection was triggered by something you did! Anyway, it"
                           ." was detected and blocked. \n";
        $output        .=        "The suspicious activity was recognized in $detecting_file "
                              ."on line $detecting_line, and is of the type $hack_type. \n";
        $output        .=        "Additional information given by the code which detected this: ".$message;
        $output        .=        "\n\nBelow you will find a lot of information obtained about "
                           ."this attempt, that may help you to find  what happened and "
                           ."maybe who did it.\n\n";

        $output        .=        "\n=====================================\n";
        $output        .=        "Information about this user:\n";
        $output        .=        "=====================================\n";

        if ( !pnUserLoggedIn() ) {
                $output        .=  "This person is not logged in.\n";
        } else {
                $output .=        "Postnuke username:  ".pnUserGetVar('uname') ."\n"
                                   ."Registered email of this Postnuke user: ". pnUserGetVar('email')."\n"
                                   ."Registered real name of this Postnuke user: ".pnUserGetVar('name') ."\n";
        }

        $output        .=        "IP numbers: [note: when you are dealing with a real cracker "
                           ."these IP numbers might not be from the actual computer he is "
                           ."working on]"
                           ."\n\t IP according to HTTP_CLIENT_IP: ".getenv( 'HTTP_CLIENT_IP' )
                           ."\n\t IP according to REMOTE_ADDR: ".getenv( 'REMOTE_ADDR' )
                           ."\n\t IP according to GetHostByName(\$REMOTE_ADDR): ".GetHostByName( $REMOTE_ADDR )
                           ."\n\n";

        $output .=        "\n=====================================\n";
        $output .=        "Information in the \$_REQUEST array\n";
        $output .=        "=====================================\n";

        while ( list ( $key, $value ) = each ( $_rv ) ) {
                $output .= "REQUEST * $key : $value\n";
        }

        $output .=        "\n=====================================\n";
        $output .=        "Information in the \$_GET array\n";
        $output .=        "This is about variables that may have been ";
        $output .=        "in the URL string or in a 'GET' type form.\n";
        $output        .=        "=====================================\n";

        while ( list ( $key, $value ) = each ( $_gv ) ) {
                $output .= "GET * $key : $value\n";
        }

        $output        .=        "\n=====================================\n";
        $output        .=        "Information in the \$_POST array\n";
        $output        .=        "This is about visible and invisible form elements.\n";
        $output        .=        "=====================================\n";

        while ( list ( $key, $value ) = each ( $_pv ) ) {
                $output .= "POST * $key : $value\n";
        }

        $output        .=        "\n=====================================\n";
        $output        .=         "Browser information\n";
        $output        .=        "=====================================\n";

        global $HTTP_USER_AGENT;
        $output        .=        "HTTP_USER_AGENT: ".$HTTP_USER_AGENT ."\n";

        $browser = (array) get_browser();
        while ( list ( $key, $value ) = each ( $browser ) ) {
                $output .= "BROWSER * $key : $value\n";
        }

        $output        .=        "\n=====================================\n";
        $output        .=        "Information in the \$_SERVER array\n";
        $output        .=        "=====================================\n";

        while ( list ( $key, $value ) = each ( $_sv ) ) {
                $output .= "SERVER * $key : $value\n";
        }

        $output        .=        "\n=====================================\n";
        $output        .=        "Information in the \$_ENV array\n";
        $output        .=        "=====================================\n";

        while ( list ( $key, $value ) = each ( $_ev ) ) {
                $output .= "ENV * $key : $value\n";
        }

        $output        .=        "\n=====================================\n";
        $output        .=  "Information in the \$_COOKIE array\n";
        $output        .=        "=====================================\n";

        while ( list ( $key, $value ) = each ( $_cv ) )  {
                $output .= "COOKIE * $key : $value\n";
        }

        $output        .=        "\n=====================================\n";
        $output        .=        "Information in the \$_FILES array\n";
        $output        .=        "=====================================\n";

        while ( list ( $key, $value ) = each ( $_fv ) ) {
                $output .= "FILES * $key : $value\n";
        }

        $output        .=        "\n=====================================\n";
        $output        .=        "Information in the \$_SESSION array\n";
        $output .=  "This is session info. The variables\n";
        $output .=  "  starting with PNSV are PostNukeSessionVariables.\n";
        $output        .=        "=====================================\n";

        while ( list ( $key, $value ) = each ( $_snv ) ) {
                $output .= "SESSION * $key : $value\n";
        }

		$sitename = pnConfigGetVar('sitename');
		$adminmail = pnConfigGetVar('adminmail');

        $headers = "From: $sitename <$adminmail>\n"
                          ."X-Priority: 1 (Highest)\n";

        pnMail($adminmail, 'Attempted hack on your site? (type: '.$hack_type.')', $output, $headers );

        return;
}

?>
