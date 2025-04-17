<?php

// $Id$
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2001 by the Post-Nuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// PHP-NUKE Web Portal System - http://phpnuke.org/
// Thatware - http://thatware.org/
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
// Purpose of file: Module variable handling
// ----------------------------------------------------------------------

/*
 * pnModGetVar - get a module variable
 * Takes two parameters:
 * - the name of the module
 * - the name of the variable
 */
function pnModGetVar($modname, $name)
{
    if ((empty($modname)) || (empty($name))) {
        return false;
    }

    global $pnmodvar;
    if (isset($pnmodvar[$modname][$name])) {
        return $pnmodvar[$modname][$name];
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $modulevarstable = $pntable['module_vars'];
    $modulevarscolumn = &$pntable['module_vars_column'];
    $query = "SELECT $modulevarscolumn[value]
              FROM $modulevarstable
              WHERE $modulevarscolumn[modname] = '" . pnVarPrepForStore($modname) . "'
              AND $modulevarscolumn[name] = '" . pnVarPrepForStore($name) . "'";
    $result = $dbconn->Execute($query);

    if ($dbconn->ErrorNo() != 0) {
        return;
    }

    if ($result->EOF) {
        $pnmodvar[$modname][$name] = false;
        return;
    }

    list($value) = $result->fields;
    $result->Close();

    $pnmodvar[$modname][$name] = $value;
    return $value;
}

/*
 * pnModSetVar - set a module variable
 * Takes three parameters:
 * - the name of the module
 * - the name of the variable
 * - the value of the variable
 */
function pnModSetVar($modname, $name, $value)
{
    if ((empty($modname)) || (empty($name))) {
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $curvar = pnModGetVar($modname, $name);

    $modulevarstable = $pntable['module_vars'];
    $modulevarscolumn = &$pntable['module_vars_column'];
    if (!isset($curvar)) {
        $query = "INSERT INTO $modulevarstable
                     ($modulevarscolumn[modname],
                      $modulevarscolumn[name],
                      $modulevarscolumn[value])
                  VALUES
                     ('" . pnVarPrepForStore($modname) . "',
                      '" . pnVarPrepForStore($name) . "',
                      '" . pnVarPrepForStore($value) . "');";
    } else {
        $query = "UPDATE $modulevarstable
                  SET $modulevarscolumn[value] = '" . pnVarPrepForStore($value) . "'
                  WHERE $modulevarscolumn[modname] = '" . pnVarPrepForStore($modname) . "'
                  AND $modulevarscolumn[name] = '" . pnVarPrepForStore($name) . "'";
    }

    $dbconn->Execute($query);

    if ($dbconn->ErrorNo() != 0) {
        return;
    }

    global $pnmodvar;
    $pnmodvar[$modname][$name] = $value;
    return true;
}


/*
 * pnModGetIDFromName - get module ID given its name
 * Takes one parameter:
 * - the name of the module
 */
function pnModGetIDFromName($module)
{
    if (empty($module)) {
        return false;
    }

    static $modid = array();
    if (isset($modid[$module])) {
        return $modid[$module];
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];
    $query = "SELECT $modulescolumn[id]
              FROM $modulestable
              WHERE $modulescolumn[name] = '" . pnVarPrepForStore($module) . "'";
    $result = $dbconn->Execute($query);

    if ($dbconn->ErrorNo() != 0) {
        return;
    }

    if ($result->EOF) {
        $modid[$module] = false;
        return false;
    }

    list($id) = $result->fields;
    $result->Close();

    $modid[$module] = $id;
    return $id;
}

/**
 * get information on module
 * @param id
 * @returns array
 * @ return array of module information or false if core ( id = 0 )
 */
function pnModGetInfo($modid)
{
    // a $modid of 0 is associated with core ( pn_blocks.mid, ... ).
    if ($modid == 0) {
        return false;
    }

    static $modinfo = array();
    if (isset($modinfo[$modid])) {
        return $modinfo[$modid];
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];
    $query = "SELECT $modulescolumn[name],
                     $modulescolumn[type],
                     $modulescolumn[directory],
                     $modulescolumn[regid],
                     $modulescolumn[displayname],
                     $modulescolumn[description],
                     $modulescolumn[version]
              FROM $modulestable
              WHERE $modulescolumn[id] = '" . pnVarPrepForStore($modid) . "'";
    $result = $dbconn->Execute($query);

    if ($dbconn->ErrorNo() != 0) {
        return;
    }

    if ($result->EOF) {
        $modinfo[$modid] = false;
        return false;
    }

    list($resarray['name'],
         $resarray['type'],
         $resarray['directory'],
         $resarray['regid'],
         $resarray['displayname'],
         $resarray['description'],
         $resarray['version']) = $result->fields;
    $result->Close();

    $modinfo[$modid] = $resarray;
    return $resarray;
}


/**
 * load an API for a module
 * @param modname - registered name of the module
 * @param type - type of functions to load
 * @returns bool
 * @return true on success, false on failure
 */
function pnModAPILoad($modname, $type = 'user')
{
    static $loaded = array();

    if (empty($modname)) {
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    if (!empty($loaded["$modname$type"])) {
        // Already loaded from somewhere else
        return true;
    }

    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];
    $query = "SELECT $modulescolumn[name],
                     $modulescolumn[directory],
                     $modulescolumn[state]
              FROM $modulestable
              WHERE $modulescolumn[name] = '" . pnVarPrepForStore($modname) . "'";
    $result = $dbconn->Execute($query);

    if ($dbconn->ErrorNo() != 0) {
        return;
    }

    if ($result->EOF) {
        return false;
    }

    list($name, $directory, $state) = $result->fields;
    $result->Close();

    list($osdirectory, $ostype) = pnVarPrepForOS($directory, $type);

    $osfile = "modules/$osdirectory/pn{$ostype}api.php";
    if (!file_exists($osfile)) {
        // File does not exist
        return false;
    }

    // Load the file
    require $osfile;
    $loaded["$modname$type"] = 1;

    if (file_exists("modules/$osdirectory/pnlang/eng/{$ostype}api.php")) {
        require "modules/$osdirectory/pnlang/eng/{$ostype}api.php";
    }

    // Load datbase info
    pnModDBInfoLoad($modname, $directory);

    return true;
}

/**
 * load datbase definition for a module
 * @param name - name of module to load database definition for
 * @param directory - directory that module is in (if known)
 * @returns bool
 */
function pnModDBInfoLoad($modname, $directory = '')
{
    static $loaded = array();

    // Check to ensure we aren't doing this twice
    if (isset($loaded[$modname])) {
        return true;
    }

    // Get the directory if we don't already have it
    if (empty($directory)) {
        list($dbconn) = pnDBGetConn();
        $pntable = pnDBGetTables();
        $modulestable = $pntable['modules'];
        $modulescolumn = &$pntable['modules_column'];
        $sql = "SELECT $modulescolumn[directory]
                FROM $modulestable
                WHERE $modulescolumn[name] = '" . pnVarPrepForStore($modname) . "'";
        $result = $dbconn->Execute($sql);
        if ($dbconn->ErrorNo() != 0) {
            return;
        }

        if ($result->EOF) {
            return false;
        }

        $directory = $result->fields[0];
        $result->Close();
    }

    // Load the database definition if required
    $ospntablefile = 'modules/' . pnVarPrepForOS($directory) . '/pntables.php';
    // Ignore errors for this, if it fails we'll find out and handle
    // it when we look for the function itself
    @include_once $ospntablefile;
    $tablefunc = $modname . '_' . 'pntables';
    if (function_exists($tablefunc)) {
        global $pntable;
        $pntable = array_merge($pntable, $tablefunc());
    }

    $loaded[$modname] = true;

    return true;
}

/**
 * load a module
 * @param name - name of module to load
 * @param type - type of functions to load
 * @returns string
 * @return name of module loaded, or false on failure
 */
function pnModLoad($modname, $type = 'user')
{
    static $loaded = array();

    if (empty($modname)) {
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];

    if (!empty($loaded["$modname$type"])) {
        // Already loaded from somewhere else
        return $modname;
    }

    $query = "SELECT $modulescolumn[directory],
                     $modulescolumn[state]
              FROM $modulestable
              WHERE $modulescolumn[name] = '" . pnVarPrepForStore($modname) . "'";
    $result = $dbconn->Execute($query);

    if ($dbconn->ErrorNo() != 0) {
        return;
    }

    if ($result->EOF) {
        return false;
    }

    list($directory, $state) = $result->fields;
    $result->Close();

    // Load the module and module language files
    list($osdirectory, $ostype) = pnVarPrepForOS($directory, $type);
    $osfile = "modules/$osdirectory/pn$ostype.php";

    if (!file_exists($osfile)) {
        // File does not exist
        return false;
    }

    // Load file
    require $osfile;
    $loaded["$modname$type"] = 1;

    if (file_exists("modules/$osdirectory/pnlang/eng/$ostype.php")) {
        require "modules/$osdirectory/pnlang/eng/$ostype.php";
    }

    // Load datbase info
    pnModDBInfoLoad($modname, $directory);

    // Return the module name
    return $modname;
}

/**
 * run a module API function
 * @param modname - registered name of module
 * @param type - type of function to run
 * @param func - specific function to run
 * @param args - arguments to pass to the function
 * @returns mixed
 */
function pnModAPIFunc($modname, $type, $func, $args = array())
{

    if (empty($modname)) {
        return false;
    }

    if (empty($type)) {
        return false;
    }

    if (empty($func)) {
        return false;
    }

    // Build function name and call function
    $modapifunc = "{$modname}_{$type}api_{$func}";
    if (function_exists($modapifunc)) {
        return $modapifunc($args);
    }

    return false;
}

/**
 * run a module function
 * @param modname - registered name of module
 * @param type - type of function to run
 * @param func - specific function to run
 * @param args - argument array
 * @returns mixed
 */
function pnModFunc($modname, $type, $func, $args = array())
{

    if (empty($modname)) {
        return false;
    }

    if (empty($type)) {
        return false;
    }

    if (empty($func)) {
        return false;
    }

    // Build function name and call function
    $modfunc = "{$modname}_{$type}_{$func}";
    if (function_exists($modfunc)) {
        return $modfunc($args);
    }

    return false;
}

/**
 * generate a module function URL
 * @param modname - registered name of module
 * @param type - type of function
 * @param func - module function
 * @param args - array of arguments to put on the URL
 * @returns string
 * @return absolute URL for call
 */
function pnModURL($modname, $type = 'user', $func = 'main', $args = array(), $path = '')
{
    if (empty($modname)) {
        return false;
    }

    global $HTTP_SERVER_VARS;

    // Hostname
    $host = $HTTP_SERVER_VARS['HTTP_HOST'] ?? '';
    if (empty($host)) {
        $host = getenv('HTTP_HOST');
        if (empty($host)) {
            return false;
        }
    }

    // The arguments
    $urlargs[] = "module=$modname";
    if ((!empty($type)) && ($type != 'user')) {
        $urlargs[] = "type=$type";
    }

    if ((!empty($func)) && ($func != 'main')) {
        $urlargs[] = "func=$func";
    }

    $urlargs = join('&', $urlargs);
    $url = "index.php?$urlargs";


    // <rabbitt> added array check on args
    // April 11, 2003
    if (!is_array($args)) {
        return false;
    } else {
        foreach ($args as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $l => $w) {
                    $url .= "&" . attr($k) . "[" . attr($l) . "]=" . attr($w);
                }
            } else {
                $url .= "&" . attr($k) . "=" . attr($v);
            }
        }
    }

    //remove characters not belonging in a path, prevent possible injection
    //this may break windows path accesses?
    $path = preg_replace("/[^\.\/a-zA-Z0-9]/", "", $path);

    // The URL
    $final_url = pnGetBaseURL() . $path . $url;
    return $final_url;
}

/**
 * see if a module is available
 * @returns bool
 * @return true if the module is available, false if not
 */
function pnModAvailable($modname)
{
    if (empty($modname)) {
        return false;
    }

    static $modstate = array();
    if (isset($modstate[$modname])) {
        if ($modstate[$modname] == _PNMODULE_STATE_ACTIVE) {
            return true;
        } else {
            return false;
        }
    }

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];
    $query = "SELECT $modulescolumn[state]
              FROM $modulestable
              WHERE $modulescolumn[name] = '" . pnVarPrepForStore($modname) . "'";
    $result = $dbconn->Execute($query);

    if ($dbconn->ErrorNo() != 0) {
        return;
    }

    if ($result->EOF) {
        $modstate[$modname] = _PNMODULE_STATE_MISSING;
        return false;
    }

    list($state) = $result->fields;
    $result->Close();

    $modstate[$modname] = $state;
    if ($state == _PNMODULE_STATE_ACTIVE) {
        return true;
    } else {
        return false;
    }
}
