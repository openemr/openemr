<?php
require_once($GLOBALS["webserver_root"].'/interface/globals.php');
require_once($GLOBALS["webserver_root"].'/library/acl_upgrade_fx.php');

//Ensure that phpGACL has been installed
require_once($GLOBALS["webserver_root"].'/library/acl.inc');

if (isset($phpgacl_location)) {
    include_once("$phpgacl_location/gacl_api.class.php");
    $gacl = new gacl_api();
} else {
    die("You must first set up library/acl.inc to use phpGACL!");
}

addObjectSectionAcl('pfeh', 'PatientFilterEventHook');

addObjectAcl('pfeh', 'Fields Filter', 'field_filter', 'Fields Filter');
addObjectAcl('pfeh', 'Hook Filter', 'hooks', 'Hook Filter');
addObjectAcl('pfeh', 'Parameters', 'params', 'Parameters');

$physicians_write = getAclIdNumber('Physicians', 'write');
updateAcl($physicians_write, 'Physicians', 'pfeh', 'Fields Filter', 'field_filter', 'Fields Filter', 'write');
updateAcl($physicians_write, 'Physicians', 'pfeh', 'Hook Filter', 'hooks', 'Hook Filter', 'write');
updateAcl($physicians_write, 'Physicians', 'pfeh', 'Parameters', 'params', 'Parameters', 'write');

?>
<html>
<head>
    <title>PatientFilterEventHookTest ACL Setup</title>
    <link rel=STYLESHEET href="interface/themes/style_blue.css">
</head>
<body>
<b>OpenEMR[PatientFilterEventHookTest] ACL Setup</b>
<br>
All done configuring and installing access controls (php-GACL)!
</body>
</html>

