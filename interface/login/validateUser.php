<?php
//VicarePlus :: This file checks the hashing algorithm used for the password in the initial login.
//VicarePlus :: This file is called by a jquery function in login.php

// Use new security method
$fake_register_globals=false;
$sanitize_all_escapes=true;

$ignoreAuth=true;
include_once("../globals.php");
include_once("$srcdir/sql.inc");
$user = $_GET['u'];
$authDB = sqlQuery("select length(password) as passlength from users where username = ?", array($user) );
$passlength = $authDB['passlength'];
//VicarePlus :: If the length of the password is 32, 'md5' hashing algorithm is used.
if ($passlength == 32)
{
echo "0";
}
//VicarePlus :: If the length of the password is 40, 'sha1' hashing algorithm is used.
else if($passlength == 40)
{
echo "1";
}
?>
