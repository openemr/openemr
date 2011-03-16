<?php
//VicarePlus :: This file checks the hashing algorithm used for the password in the initial login.
//VicarePlus :: This file is called by a jquery function in login.php
$ignoreAuth=true;
include_once("../globals.php");
include_once("$srcdir/sql.inc");
$user = $_GET['u'];
$authDB = sqlQuery("select password,length(password) as passlength from users where username = '$user'");
$passlength = $authDB['passlength'];
$pw = $authDB['password'];
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
