<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * This is a shortcut for htmlspecial vars.
 * @param <type> $txt 
 */
function out( $txt ) {
    return htmlspecialchars( $txt , ENT_QUOTES );
}

/**
 * This is a wrapper for implode function, which calls each function in the 
 * array $funcs on each piece in the array $pieces
 * @param <type> $glue
 * @param <type> $pieces
 * @param <type> $funcs
 */
function implode_funcs( $glue, array $pieces, array $funcs ) {
    $new_pieces = array();
    foreach ( $pieces as $piece ) {
        $new_piece = $piece;
        foreach ( $funcs as $func ) {
            $new_piece = $func( $new_piece );
        }
        $new_pieces []= $new_piece;
    }
    
    return implode( $glue, $new_pieces );
}

/**
 * * xxx todo: sanitize inputs
 * @param <type> $var
 * @param <type> $default
 * @return <type> 
 */
function _get( $var, $default='' ) {
    $val = $_GET[$var];
    return isset($val) && $val != '' ? $val : $default;
}

/**
 * xxx todo: sanitize inputs
 * @param <type> $var
 * @param <type> $default
 * @return <type>
 */
function _post( $var, $default='' ) {
    $val = $_POST[$var];
    return isset($val) && $val != '' ? $val : $default;
}

function _base_url() {
    return $GLOBALS['webroot'] . '/interface/super/rules';
}

function src_dir() {
    return $GLOBALS['srcdir'];
}

function base_dir() {
    return dirname(__FILE__) . "/../";
}

function library_dir() {
    return base_dir() . '/library';
}

function library_src( $file ) {
    return library_dir() . "/$file";
}

function js_src( $file ) {
    echo _base_url() . '/www/js/' . $file;
}

function css_src( $file ) {
    echo _base_url() . '/www/css/' . $file;
}

function controller_basedir() {
    return realpath( base_dir() . '/controllers/' );
}
function controller_dir( $controller ) {
    $dir = controller_basedir() . '/'. $controller;
    if ( realpath( $dir . '/../') != controller_basedir() )  {
        throw Exception("Invalid controller '$controller'");
    }
    return $dir;
}

?>
