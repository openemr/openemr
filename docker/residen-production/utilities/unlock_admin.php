<?php

// unlocks the admin user and changes that password from the default of 'pass'
// to a user-supplied password

$newPassword = $argv[1];
$_GET['site'] = 'default';
$ignoreAuth=1;
require_once("/var/www/localhost/htdocs/openemr/interface/globals.php");

use OpenEMR\Common\Auth\AuthUtils;

$GLOBALS['secure_password'] = 0;

sqlStatement("UPDATE `users` SET `active` = 1 WHERE `id` = 1");

$currentPassword = "pass";
$unlockUpdatePassword = new AuthUtils();
$unlockUpdatePassword->updatePassword(1, 1, $currentPassword, $newPassword);
if (!empty($unlockUpdatePassword->getErrorMessage())) {
    echo "ERROR: " . $unlockUpdatePassword->getErrorMessage() . "\n";
}
