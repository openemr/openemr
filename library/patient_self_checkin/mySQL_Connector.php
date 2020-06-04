<?php
$ignoreAuth = true;
require_once "../../interface/globals.php";
require_once $GLOBALS['webserver_root'] . '/sites/default/sqlconf.php';

// Localhost testing server
DEFINE('DB_USER', $login);
DEFINE('DB_PASSWORD', $pass);
DEFINE('DB_HOST', $host .":". $port);
DEFINE('DB_NAME', $dbase);

$dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
or die();
