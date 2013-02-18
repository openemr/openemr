<?php

//error_reporting(1);
set_time_limit(0);

extract($GLOBALS['sqlconf']);
define('DB_SERVER', $host); // eg, localhost - should not be empty for productive servers
define('DB_USERNAME', $login);
define('DB_PASSWORD', $pass);
define('DB_DATABASE', $dbase);

// ezSQL Constants
define("EZSQL_VERSION", "1.26");
define("OBJECT", "OBJECT", true);
define("ARRAY_A", "ARRAY_A", true);
define("ARRAY_N", "ARRAY_N", true);
?>