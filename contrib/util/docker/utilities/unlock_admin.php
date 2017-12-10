<?php

// unlocks the admin user and changes that password from the default of 'pass'
// to a user-supplied password

$newPassword = $argv[1];
$_GET['site'] = 'default';
$ignoreAuth=1;
require_once("/var/www/localhost/htdocs/openemr/interface/globals.php");
require_once($GLOBALS['srcdir'] . "/authentication/password_change.php");

sqlStatement("UPDATE `users` SET `active` = 1 WHERE `id` = 1");

$currentPassword = "pass";
$catchErrorMessage = "";
update_password(1, 1, $currentPassword, $newPassword, $catchErrorMessage);
if (!empty($catchErrorMessage)) {
    echo "ERROR: " . $catchErrorMessage . "\n";
}
