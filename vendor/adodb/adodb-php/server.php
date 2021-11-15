<?php

/**
 * @version   v5.21.0  2021-02-27
 * @copyright (c) 2000-2013 John Lim (jlim#natsoft.com). All rights reserved.
 * @copyright (c) 2014      Damien Regad, Mark Newnham and the ADOdb community
 * Released under both BSD license and Lesser GPL library license.
  Whenever there is any discrepancy between the two licenses,
  the BSD license will take precedence.
 */

/* Documentation on usage is at https://adodb.org/dokuwiki/doku.php?id=v5:proxy:proxy_index
 *
 * Legal query string parameters:
 *
 * sql = holds sql string
 * nrows = number of rows to return
 * offset = skip offset rows of data
 * fetch = $ADODB_FETCH_MODE
 *
 * example:
 *
 * http://localhost/php/server.php?sql=select+*+from+table&nrows=10&offset=2
 */


/*
 * Define the IP address you want to accept requests from
 * as a security measure. If blank we accept anyone promisciously!
 */
$ACCEPTIP = '127.0.0.1';

/*
 * Connection parameters
 */
$driver = 'mysqli';
$host = 'localhost'; // DSN for odbc
$uid = 'root';
$pwd = 'garbase-it-is';
$database = 'test';

/*============================ DO NOT MODIFY BELOW HERE =================================*/
// $sep must match csv2rs() in adodb.inc.php
$sep = ' :::: ';

include('./adodb.inc.php');
include_once(ADODB_DIR.'/adodb-csvlib.inc.php');

function err($s)
{
	die('**** '.$s.' ');
}

///////////////////////////////////////// DEFINITIONS


$remote = $_SERVER["REMOTE_ADDR"];


if (!empty($ACCEPTIP))
 if ($remote != '127.0.0.1' && $remote != $ACCEPTIP)
 	err("Unauthorised client: '$remote'");


if (empty($_REQUEST['sql'])) err('No SQL');


$conn = ADONewConnection($driver);

if (!$conn->connect($host,$uid,$pwd,$database)) err($conn->errorNo(). $sep . $conn->errorMsg());
$sql = $_REQUEST['sql'];

if (isset($_REQUEST['fetch']))
	$ADODB_FETCH_MODE = $_REQUEST['fetch'];

if (isset($_REQUEST['nrows'])) {
	$nrows = $_REQUEST['nrows'];
	$offset = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : -1;
	$rs = $conn->selectLimit($sql,$nrows,$offset);
} else
	$rs = $conn->execute($sql);
if ($rs){
	//$rs->timeToLive = 1;
	echo _rs2serialize($rs,$conn,$sql);
	$rs->close();
} else
	err($conn->errorNo(). $sep .$conn->errorMsg());
