<?php
//  OpenEMR
//  MySQL Config

$host   = 'localhost';
$port   = '3306';
$login  = 'openemr';
$pass   = 'openemr';
$dbase  = 'openemr';
$db_encoding = 'utf8mb4';

$sqlconf = [];
global $sqlconf;
$sqlconf["host"]= $host;
$sqlconf["port"] = $port;
$sqlconf["login"] = $login;
$sqlconf["pass"] = $pass;
$sqlconf["dbase"] = $dbase;
$sqlconf["db_encoding"] = $db_encoding;

//////////////////////////
//////////////////////////
//////////////////////////
//////DO NOT TOUCH THIS///
$config = 0; /////////////
//////////////////////////
//////////////////////////
//////////////////////////
