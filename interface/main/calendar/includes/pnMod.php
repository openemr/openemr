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

function pnModBuildLegacyQuery($conn, string $query, array $params = []): string
{
    foreach ($params as $param) {
        if ($param === null) {
            $replacement = 'NULL';
        } elseif (is_numeric($param) && !is_string($param)) {
            $replacement = (string)$param;
        } else {
            $replacement = method_exists($conn, 'qstr')
                ? $conn->qstr((string)$param)
                : ("'" . addslashes((string)$param) . "'");
        }
        $query = preg_replace('/\?/', $replacement, $query, 1);
    }
    return $query;
}

function pnModFetchNumeric($conn, string $query, array $params = [])
{
    if (is_object($conn) && method_exists($conn, 'fetchNumeric')) {
        return $conn->fetchNumeric($query, $params);
    }
    if (is_object($conn) && method_exists($conn, 'Execute')) {
        $result = $conn->Execute(pnModBuildLegacyQuery($conn, $query, $params));
        if (!$result || $result->EOF) {
            return false;
        }
        $row = array_values((array)($result->fields ?? []));
        if (method_exists($result, 'Close')) {
            $result->Close();
        }
        return $row;
    }
    $result = sqlStatement($query, $params);
    $row = sqlFetchArray($result);
    return $row ? array_values($row) : false;
}

function pnModFetchAssoc($conn, string $query, array $params = [])
{
    if (is_object($conn) && method_exists($conn, 'fetchAssociative')) {
        $row = $conn->fetchAssociative($query, $params);
        return $row ?: false;
    }
    if (is_object($conn) && method_exists($conn, 'Execute')) {
        $result = $conn->Execute(pnModBuildLegacyQuery($conn, $query, $params));
        if (!$result || $result->EOF) {
            return false;
        }
        $row = (array)($result->fields ?? []);
        $assoc = [];
        foreach ($row as $key => $value) {
            if (!is_int($key) && !ctype_digit((string)$key)) {
                $assoc[(string)$key] = $value;
            }
        }
        if (method_exists($result, 'Close')) {
            $result->Close();
        }
        return $assoc ?: false;
    }
    $result = sqlStatement($query, $params);
    $row = sqlFetchArray($result);
    return $row ?: false;
}

function pnModFetchOne($conn, string $query, array $params = [])
{
    if (is_object($conn) && method_exists($conn, 'fetchOne')) {
        return $conn->fetchOne($query, $params);
    }
    if (is_object($conn) && method_exists($conn, 'GetOne')) {
        return $conn->GetOne(pnModBuildLegacyQuery($conn, $query, $params));
    }
    if (is_object($conn) && method_exists($conn, 'Execute')) {
        $result = $conn->Execute(pnModBuildLegacyQuery($conn, $query, $params));
        if (!$result || $result->EOF) {
            return false;
        }
        $fields = (array)($result->fields ?? []);
        $value = $fields[0] ?? (array_values($fields)[0] ?? false);
        if (method_exists($result, 'Close')) {
            $result->Close();
        }
        return $value;
    }
    $row = sqlQuery($query, $params);
    return $row ? (array_values($row)[0] ?? false) : false;
}

function pnModExecuteStatement($conn, string $query, array $params = []): bool
{
    if (is_object($conn) && method_exists($conn, 'executeStatement')) {
        return (bool)$conn->executeStatement($query, $params);
    }
    if (is_object($conn) && method_exists($conn, 'Execute')) {
        return (bool)$conn->Execute(pnModBuildLegacyQuery($conn, $query, $params));
    }
    sqlStatement($query, $params);
    return true;
}

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

    $conn = pnDBGetConn();
    if (is_array($conn) && isset($conn[0])) {
        $conn = $conn[0];
    }
    $pntable = pnDBGetTables();

    $modulevarstable = $pntable['module_vars'];
    $modulevarscolumn = &$pntable['module_vars_column'];
    $query = "SELECT $modulevarscolumn[value]
              FROM $modulevarstable
              WHERE $modulevarscolumn[modname] = ?
              AND $modulevarscolumn[name] = ?";
    try {
        $value = pnModFetchOne($conn, $query, [$modname, $name]);
    } catch (Doctrine\DBAL\Exception) {
        return;
    }

    if ($value === false) {
        $pnmodvar[$modname][$name] = false;
        return;
    }

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

    $conn = pnDBGetConn();
    if (is_array($conn) && isset($conn[0])) {
        $conn = $conn[0];
    }
    $pntable = pnDBGetTables();

    $curvar = pnModGetVar($modname, $name);

    $modulevarstable = $pntable['module_vars'];
    $modulevarscolumn = &$pntable['module_vars_column'];
    try {
        if (!isset($curvar)) {
            $query = "INSERT INTO $modulevarstable
                         ($modulevarscolumn[modname],
                          $modulevarscolumn[name],
                          $modulevarscolumn[value])
                      VALUES (?, ?, ?)";
            pnModExecuteStatement($conn, $query, [$modname, $name, $value]);
        } else {
            $query = "UPDATE $modulevarstable
                      SET $modulevarscolumn[value] = ?
                      WHERE $modulevarscolumn[modname] = ?
                      AND $modulevarscolumn[name] = ?";
            pnModExecuteStatement($conn, $query, [$value, $modname, $name]);
        }
    } catch (Doctrine\DBAL\Exception) {
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

    static $modid = [];
    if (isset($modid[$module])) {
        return $modid[$module];
    }

    $conn = pnDBGetConn();
    if (is_array($conn) && isset($conn[0])) {
        $conn = $conn[0];
    }
    $pntable = pnDBGetTables();

    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];
    $query = "SELECT $modulescolumn[id]
              FROM $modulestable
              WHERE $modulescolumn[name] = ?";
    try {
        $id = pnModFetchOne($conn, $query, [$module]);
    } catch (Doctrine\DBAL\Exception) {
        return;
    }

    if ($id === false) {
        $modid[$module] = false;
        return false;
    }

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

    static $modinfo = [];
    if (isset($modinfo[$modid])) {
        return $modinfo[$modid];
    }

    $conn = pnDBGetConn();
    if (is_array($conn) && isset($conn[0])) {
        $conn = $conn[0];
    }
    $pntable = pnDBGetTables();

    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];
    $query = "SELECT $modulescolumn[name] AS name,
                     $modulescolumn[type] AS type,
                     $modulescolumn[directory] AS directory,
                     $modulescolumn[regid] AS regid,
                     $modulescolumn[displayname] AS displayname,
                     $modulescolumn[description] AS description,
                     $modulescolumn[version] AS version
              FROM $modulestable
              WHERE $modulescolumn[id] = ?";
    try {
        $row = pnModFetchAssoc($conn, $query, [$modid]);
    } catch (Doctrine\DBAL\Exception) {
        return;
    }

    if ($row === false) {
        $modinfo[$modid] = false;
        return false;
    }

    $resarray = [
        'name' => $row['name'] ?? '',
        'type' => $row['type'] ?? '',
        'directory' => $row['directory'] ?? '',
        'regid' => $row['regid'] ?? '',
        'displayname' => $row['displayname'] ?? '',
        'description' => $row['description'] ?? '',
        'version' => $row['version'] ?? '',
    ];

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
    static $loaded = [];

    if (empty($modname)) {
        return false;
    }

    $conn = pnDBGetConn();
    if (is_array($conn) && isset($conn[0])) {
        $conn = $conn[0];
    }
    $pntable = pnDBGetTables();

    if (!empty($loaded["$modname$type"])) {
        // Already loaded from somewhere else
        return true;
    }

    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];
    $query = "SELECT $modulescolumn[name] AS name,
                     $modulescolumn[directory] AS directory,
                     $modulescolumn[state] AS state
              FROM $modulestable
              WHERE $modulescolumn[name] = ?";
    try {
        $row = pnModFetchAssoc($conn, $query, [$modname]);
    } catch (Doctrine\DBAL\Exception $e) {
        return;
    }

    if ($row === false) {
        return false;
    }

    $name = $row['name'] ?? '';
    $directory = $row['directory'] ?? '';
    $state = $row['state'] ?? null;
    if ($directory === '') {
        return false;
    }

    [$osdirectory, $ostype] = pnVarPrepForOS($directory, $type);

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

    // Load database info
    pnModDBInfoLoad($modname, $directory);

    return true;
}

/**
 * load database definition for a module
 * @param name - name of module to load database definition for
 * @param directory - directory that module is in (if known)
 * @returns bool
 */
function pnModDBInfoLoad($modname, $directory = '')
{
    static $loaded = [];

    // Check to ensure we aren't doing this twice
    if (isset($loaded[$modname])) {
        return true;
    }

    // Get the directory if we don't already have it
    if (empty($directory)) {
        $conn = pnDBGetConn();
        if (is_array($conn) && isset($conn[0])) {
            $conn = $conn[0];
        }
        $pntable = pnDBGetTables();
        $modulestable = $pntable['modules'];
        $modulescolumn = &$pntable['modules_column'];
        $sql = "SELECT $modulescolumn[directory]
                FROM $modulestable
                WHERE $modulescolumn[name] = ?";
        try {
            $directory = pnModFetchOne($conn, $sql, [$modname]);
        } catch (Doctrine\DBAL\Exception) {
            return false;
        }
        if ($directory === false) {
            return false;
        }
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
 * @return string|false|null name of module loaded, or false on failure
 */
function pnModLoad($modname, $type = 'user')
{
    static $loaded = [];

    if (empty($modname)) {
        return false;
    }

    $conn = pnDBGetConn();
    if (is_array($conn) && isset($conn[0])) {
        $conn = $conn[0];
    }
    $pntable = pnDBGetTables();

    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];

    if (!empty($loaded["$modname$type"])) {
        // Already loaded from somewhere else
        return $modname;
    }

    $query = "SELECT $modulescolumn[directory] AS directory,
                     $modulescolumn[state] AS state
              FROM $modulestable
              WHERE $modulescolumn[name] = ?";
    try {
        $row = pnModFetchAssoc($conn, $query, [$modname]);
    } catch (Doctrine\DBAL\Exception $e) {
        return;
    }

    if ($row === false) {
        return false;
    }

    $directory = $row['directory'] ?? '';
    $state = $row['state'] ?? null;
    if ($directory === '') {
        return false;
    }

    // Load the module and module language files
    [$osdirectory, $ostype] = pnVarPrepForOS($directory, $type);
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

    // Load database info
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
function pnModAPIFunc($modname, $type, $func, $args = [])
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
function pnModFunc($modname, $type, $func, $args = [])
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
function pnModURL($modname, $type = 'user', $func = 'main', $args = [], $path = '')
{
    if (empty($modname)) {
        return false;
    }

    // Hostname
    $host = $_SERVER['HTTP_HOST'] ?? '';
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

    $urlargs = implode('&', $urlargs);
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
    $path = preg_replace("/[^\.\/a-zA-Z0-9]/", "", (string) $path);

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

    static $modstate = [];
    if (isset($modstate[$modname])) {
        if ($modstate[$modname] == _PNMODULE_STATE_ACTIVE) {
            return true;
        } else {
            return false;
        }
    }

    $conn = pnDBGetConn();
    if (is_array($conn) && isset($conn[0])) {
        $conn = $conn[0];
    }
    $pntable = pnDBGetTables();

    $modulestable = $pntable['modules'];
    $modulescolumn = &$pntable['modules_column'];
    $query = "SELECT $modulescolumn[state]
              FROM $modulestable
              WHERE $modulescolumn[name] = ?";
    $state = false;
    if (is_object($conn) && method_exists($conn, 'fetchOne')) {
        try {
            $state = pnModFetchOne($conn, $query, [$modname]);
        } catch (\Throwable $e) {
            return false;
        }
    } elseif (is_object($conn) && method_exists($conn, 'Execute')) {
        $legacyQuery = "SELECT $modulescolumn[state]
              FROM $modulestable
              WHERE $modulescolumn[name] = '" . pnVarPrepForStore($modname) . "'";
        $result = $conn->Execute($legacyQuery);
        if (!$result || $result->EOF) {
            $state = false;
        } else {
            [$state] = $result->fields;
            $result->Close();
        }
    } else {
        $result = sqlStatement(
            "SELECT $modulescolumn[state] AS state FROM $modulestable WHERE $modulescolumn[name] = ?",
            [$modname]
        );
        $row = sqlFetchArray($result);
        if ($row) {
            $state = $row['state'] ?? (array_values($row)[0] ?? false);
        }
    }

    if ($state === false) {
        $modstate[$modname] = _PNMODULE_STATE_MISSING;
        return false;
    }

    $modstate[$modname] = $state;
    if ($state == _PNMODULE_STATE_ACTIVE) {
        return true;
    } else {
        return false;
    }
}
