<?php

// Localhost testing server
DEFINE ('DB_USER', 'alfie');
DEFINE ('DB_PASSWORD', 'mysql');
DEFINE ('DB_HOST','192.168.0.2:3307');
DEFINE ('DB_NAME', 'emis');

$dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
OR die();

?>

