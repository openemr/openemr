<?php

use Doctrine\DBAL\Connection;
use OpenEMR\BC\Database;

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
    $supers = ['_REQUEST',
                            '_ENV',
                            '_SERVER',
                            '_POST',
                            '_GET',
                            '_COOKIE',
                            '_SESSION',
                            '_FILES',
                            '_GLOBALS' ];

    foreach ($supers as $__s) {
        if ((isset(${$__s}) == true) && (is_array(${$__s}) == true)) {
            extract(${$__s}, EXTR_OVERWRITE);
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
 */
function pnConfigInit(): bool
{
    global $pnconfig;

    $conn = pnDBGetConn();
    $pntable = pnDBGetTables();

    $table = $pntable['module_vars'];
    $columns = &$pntable['module_vars_column'];

    /*
     * Make query and go
     */
    $query = "SELECT $columns[name],
                     $columns[value]
              FROM $table
              WHERE $columns[modname]= ?";
    try {
        $result = $conn->executeQuery($query, [_PN_CONFIG_MODULE]);
        $rows = $result->fetchAllNumeric();
    } catch (Doctrine\DBAL\Exception) {
        return false;
    }

    if (empty($rows)) {
        return false;
    }

    foreach ($rows as [$k, $v]) {
        if (
            !in_array($k, ['dbtype', 'dbhost', 'dbuname', 'dbpass', 'dbname', 'system', 'prefix', 'encoded'])
        ) {
            $pnconfig[$k] = $v;
        }
    }

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
        $conn = pnDBGetConn();
        $pntable = pnDBGetTables();

        $table = $pntable['module_vars'];
        $columns = &$pntable['module_vars_column'];

        /*
         * Make query and go
         */
        $query = "SELECT $columns[value]
                  FROM $table
                  WHERE $columns[modname]= ?
                    AND $columns[name]= ?";
        try {
            $value = $conn->fetchOne($query, [_PN_CONFIG_MODULE, $name]);
        } catch (Doctrine\DBAL\Exception) {
            return false;
        }

        if ($value === false) {
            return false;
        }

        /*
         * Get data
         */
        $result = unserialize($value, ['allowed_classes' => false]);

        /*
         * Some caching
         */
        $pnconfig[$name] = $result;
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

    // Initialise and load configuration
    global $pnconfig;
    $pnconfig = [];
    require 'config.php';

    // Initialise and load pntables
    global $pntable;
    $pntable = [];
    require 'pntables.php';

    // Build up old config array
    pnConfigInit();

    // Other other includes
    require 'includes/pnHTML.php';
    require 'includes/pnMod.php';

    return true;
}

function pnDBGetConn(): Connection
{
    return Database::instance()->getDbalConnection();
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
 * @return mixed prepared variable if only one variable passed in, otherwise an array of prepared variables
 */
function pnVarCleanFromInput()
{
    $search = ['|</?\s*SCRIPT.*?>|si',
                    '|</?\s*FRAME.*?>|si',
                    '|</?\s*OBJECT.*?>|si',
                    '|</?\s*META.*?>|si',
                    '|</?\s*APPLET.*?>|si',
                    '|</?\s*LINK.*?>|si',
                    '|</?\s*IFRAME.*?>|si',
                    '|STYLE\s*=\s*"[^"]*"|si'];

    $replace = [''];

    $resarray = [];
    foreach (func_get_args() as $var) {
    // Get var
        global ${$var};
        if (empty($var)) {
            return;
        }

        $ourvar = ${$var};
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
 * @return list<string|null>|string|null prepared variable if only one variable passed in, otherwise an array of prepared variables
 */
function pnVarPrepForDisplay()
{
    // This search and replace finds the text 'x@y' and replaces
    // it with HTML entities, this provides protection against
    // email harvesters
    static $search = ['/(.)@(.)/s'];

    $resarray = [];

    foreach (func_get_args() as $ourvar) {
        // Prepare var
        $ourvar = htmlspecialchars($ourvar ?? '');

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
 * @return list<string|null>|string|null prepared variable if only one variable passed in, otherwise an array of prepared variables
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
    static $search = ['/([^\024])@([^\022])/s'];

    static $allowedhtml;

    if (!isset($allowedhtml)) {
        $allowedhtml = [];
    }

    $resarray = [];
    foreach (func_get_args() as $ourvar) {
        // Preparse var to mark the HTML that we want
        $ourvar = preg_replace($allowedhtml, "\022\\1\024", (string) $ourvar);

        // Prepare var
        $ourvar = htmlspecialchars((string) $ourvar);
        $ourvar = preg_replace_callback(
            $search,
            fn($matches): string => "&#" .
            sprintf("%03d", ord($matches[1])) .
            ";&#064;&#" .
            sprintf("%03d", ord($matches[2])) . ";",
            $ourvar
        );

        // Fix the HTML that we want
        $ourvar = preg_replace_callback(
            '/\022([^\024]*)\024/',
            fn($matches): string => '<' . strtr("$matches[1]", ['&gt;' => '>', '&lt;' => '<', '&quot;' => '\"']) . '>',
            (string) $ourvar
        );

        // Fix entities if required
        if (pnConfigGetVar('htmlentities')) {
            $ourvar = preg_replace('/&amp;([a-z#0-9]+);/i', "&\\1;", (string) $ourvar);
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
 * ready database output
 * <br />
 * Gets a variable, cleaning it up such that the text is
 * stored in a database exactly as expected
 * @param var variable to prepare
 * @param ...
 * @return list<string>|string prepared variable if only one variable passed in, otherwise an array of prepared variables
 */
function pnVarPrepForStore()
{
    $resarray = [];
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
 * @return list<string>|string prepared variable if only one variable passed in, otherwise an array of prepared variables
 */
function pnVarPrepForOS()
{
    static $search = ['!\.\./!si', // .. (directory traversal)
                           '!^.*://!si', // .*:// (start of URL)
                           '!/!si',     // Forward slash (directory traversal)
                           '!\\\\!si']; // Backslash (directory traversal)

    /** @var array $replace */
    static $replace = ['',
                            '',
                            '_',
                            '_'];

    $resarray = [];
    foreach (func_get_args() as $ourvar) {
        // Parse out bad things
        $ourvar = preg_replace($search, $replace, (string) $ourvar);

        // Prepare var
        $ourvar = addslashes((string) $ourvar);

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
 *
 * @return string base URI for PostNuke
 */
function pnGetBaseURI(): string
{
    // Start of with REQUEST_URI
    $path = $_SERVER['REQUEST_URI'] ?? getenv('REQUEST_URI');

    if (empty($path) || str_ends_with((string) $path, '/')) {
        // REQUEST_URI was empty or pointed to a path
        // Try looking at PATH_INFO
        $path = getenv('PATH_INFO');
        if (empty($path)) {
            // No luck there either
            // Try SCRIPT_NAME
            $path = $_SERVER['SCRIPT_NAME'] ?? getenv('SCRIPT_NAME');
        }
    }

    $path = preg_replace('/[#\?].*/', '', (string) $path);
    $path = dirname((string) $path);

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
