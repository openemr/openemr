<?php
//  OpenEMR
//  MySQL Config
//  Referenced from sql.inc

$host	= 'localhost';
$port	= '3306';
$login	= 'openemr';
$pass	= 'openemr';
$dbase	= 'openemr';

//added ability to disable
//utf8 encoding - bm 052009
$disable_utf8_flag = false;

$sqlconf = array();
$sqlconf["host"]= $host;
$sqlconf["port"] = $port;
$sqlconf["login"] = $login;
$sqlconf["pass"] = $pass;
$sqlconf["dbase"] = $dbase;
//////////////////////////
//////////////////////////
//////////////////////////
//////DO NOT TOUCH THIS///
$config = 0; /////////////
//////////////////////////
//////////////////////////
//////////////////////////
?>
