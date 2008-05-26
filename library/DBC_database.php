<?php
/** 
 * DBC DUTCH SYSTEM
 * DATABASE FUNCTIONS - A Simple but effective framework over database operations
 * 
 * Cristian Navalici lemonsoftware [ @ ] gmail [ . ] com
 * @version 2.0 May 2008
 *
 */

// if TRUE, before every sql statement will run SET NAMES utf8
define('SETNAMES', TRUE);

//-----------------------------------------------------------------------------
/**
 * DBC SQL SELECT FUNCTION
 * 
 * @param   string	$what - what to select
 * @param   string	$from - the table to retrieve the results from
 * @param   array	the where clause
 * @param   string	the order clause
 * @param   string	the join clause
 * @param   int	        debug flag - if 1, just echo the query, don't execute anything
 * @return  resource    returns the mysql resource or FALSE
 */
function dsql_select($what, $from, $where = NULL, $order = NULL, $join = NULL, $debug = 0) {

    // make the query string
    $q = "SELECT $what FROM $from ";

    if ( $join ) {
        $q .= " JOIN $join ";
    }

    if ( $where ) {
            $wherestr   = _dsql_where($where);
            $q .= "WHERE $wherestr ";
    }
    if ( $order )    $q .= "ORDER BY $order ";

    if ( $debug ) echo $q . '<br />';
    else return _dsql_query($q);
}


//-----------------------------------------------------------------------------
/**
 * DBC SQL START TRANSACTION
 * 
 * @param   none
 * @return  bool
 */
function dsql_starttrans() {

    $q = 'START TRANSACTION';
    return _dsql_query($q);
}

//-----------------------------------------------------------------------------
/**
 * DBC SQL COMMIT TRANSACTION
 * 
 * @param   none
 * @return  bool
 */
function dsql_commit() {

    $q = 'COMMIT';
    return _dsql_query($q);
}

//-----------------------------------------------------------------------------
/**
 * DBC SQL UPDATE FUNCTION
 * 
 * @param   string	the table to retrieve the results from
 * @param   array	an associative array of update values
 * @param   array	the where clause
 * @param   int	        debug flag - if 1, just echo the query, don't execute anything
 * @return  bool
 */
function dsql_update($table, $set, $where, $debug = 0) {

    $setstr     = _dsql_set($set);
    $wherestr   = _dsql_where($where);

    // make the query string
    $q = "UPDATE $table SET $setstr WHERE $wherestr";

    if ( $debug ) echo $q . '<br />';
    else return _dsql_query($q);
}


//-----------------------------------------------------------------------------
/**
 * DBC SQL INSERT FUNCTION
 * 
 * @param   string	$table - the table to insert the values
 * @param   array	$flds - an array with table fields
 * @param   array	$val - an array with inserted  values
 * @param   int	        debug flag - if 1, just echo the query, don't execute anything
 * @return  bool
 */
function dsql_insert($table, $flds, $val, $debug = 0) {

    $fields     = _dsql_fields($flds);
    $values     = _dsql_ivalues($val);

    // make the query string
    $q = "INSERT INTO $table $fields $values";

    if ( $debug ) echo $q . '<br />';
    else return _dsql_query($q);
}


//-----------------------------------------------------------------------------
/**
 * DBC SQL INSERT ON DUPLICATE FUNCTIONS
 * 
 * @param   string	$table - the table to insert the values
 * @param   array	$flds - an array with table fields
 * @param   array	$val - an array with inserted  values
 * @param   int	        debug flag - if 1, just echo the query, don't execute anything
 * @return  bool
 */
function dsql_insert_duplicate($table, $flds, $val, $set = 0, $debug = 0) {

    $fields     = _dsql_fields($flds);
    $values     = _dsql_ivalues($val);
    $update     = _dsql_set($set);

    // make the query string
    $q = "INSERT INTO $table $fields $values ON DUPLICATE KEY UPDATE $update";

    if ( $debug ) echo $q . '<br />';
    else return _dsql_query($q);
}

//-----------------------------------------------------------------------------
/**
 * DBC SQL LAST INSERTED ID FUNCTION
 * 
 * @param   none
 * @return  int
 */
function dsql_lastid() {
    if ( isset($GLOBALS['dbh']) ) {
        // if we call from webinterface
        $r = mysql_insert_id($GLOBALS['dbh']) or die(mysql_error());
    } else {
        // if we call from CLI
        $r = mysql_insert_id() or die(mysql_error());
    }

    return $r;
}

//-----------------------------------------------------------------------------
//
//          PRIVATE FUNCTIONS BELOW
//-----------------------------------------------------------------------------


//-----------------------------------------------------------------------------
/**
 * DBC SQL QUERY FUNCTION
 * 
 * @param   string	$str - the query string
 * @return  resource|bool
 */
function _dsql_query($str) {

    if ( isset($GLOBALS['dbh']) ) {
        // if we call from webinterface
        if ( SETNAMES ) mysql_query("SET NAMES utf8", $GLOBALS['dbh']);
        $r = mysql_query($str, $GLOBALS['dbh']) or die(mysql_error());
    } else {
        // if we call from CLI
        $r = mysql_query($str) or die(mysql_error());
    }

    return $r;
}


//-----------------------------------------------------------------------------
/**
 * PREPARE THE SET STRING
 * 
 * @param   array	the array to compile
 * @return  string
 */
function _dsql_set($setarr) {
    $setstr = '';
    
    foreach ( $setarr as $sk => $sv) {
        $setstr .= " {$sk} = {$sv},"; 
    }
    $setstr = substr(trim($setstr), 0, -1); // cut the last ,

    return $setstr;
}

//-----------------------------------------------------------------------------
/**
 * PREPARE THE WHERE STRING
 * 
 * @param   array	the array to compile
 * @return  string
 */
function _dsql_where($wherearr) {
    if ( !$wherearr ) return NULL;
    $wherestr = '';

    foreach ( $wherearr as $wk => $wv) {
        // for the same key we could have an array as arg = multiple values (otherwise keys are not unique)
        if ( is_array($wv) ) {

            foreach ( $wv as $v ) {
                // if we have value starting with <=; != or => then we don't use = for them
                $first = substr(trim($v), 0, 1);
                if ( $first != '>' && $first != '<' && $first != '!' ) $sign = '=';
                else $sign = '';

                $wherestr .= " {$wk} $sign {$v} "; 
            } // foreach

        } else {

            // if we have value starting with <=; != or => then we don't use = for them
            $first = substr(trim($wv), 0, 1);
            if ( $first != '>' && $first != '<' && $first != '!' ) $sign = '=';
            else $sign = '';


            $wherestr .= " {$wk} $sign {$wv} "; 
        }
    } // foreach

    return $wherestr;
}

//-----------------------------------------------------------------------------
/**
 * PREPARE THE FIELDS STRING
 * 
 * @param   array	the array to compile
 * @return  string
 */
function _dsql_fields($fiearr) {
    if ( !is_array($fiearr) || !$fiearr ) return '';

    $fiestr = '(';
    foreach ( $fiearr as $fk => $fv) {
        $fiestr .= " {$fv}, "; 
    }
    $fiestr = substr(trim($fiestr), 0, -1); // cut the last ,
    $fiestr .= ')';

    return $fiestr;
}

//-----------------------------------------------------------------------------
/**
 * PREPARE THE VALUES STRING
 * 
 * used by insert
 *
 * @param   array	the array to compile
 * @return  string
 */
function _dsql_ivalues($values) {

    $valstr = 'VALUES (';
    foreach ( $values as $vk => $vv) {
        $valstr .= ( is_int($vv) ) ? "{$vv}, " : " '{$vv}', ";
    }
    $valstr = substr(trim($valstr), 0, -1); // cut the last ,
    $valstr .= ')';

    return $valstr;
}

//-----------------------------------------------------------------------------
?>