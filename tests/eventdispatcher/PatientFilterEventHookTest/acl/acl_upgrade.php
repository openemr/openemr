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

#upgradeFunction
function upgradeAclFromVersion($version)
{
    global $ACL_UPGRADE;
    $toVersion = '';
    foreach ($ACL_UPGRADE as $toVersion => $function) {
        if (version_compare($version, $toVersion) < 0) {
            $function();
        }
    }
    return $toVersion;
}

#
return $ACL_UPGRADE = array(

    '0.2.0' => function () {
        $physicians_write = getAclIdNumber('Physicians', 'write');
        $accounting_view  = getAclIdNumber('Accounting', 'view');
        addObjectAcl('pfeh', 'Information', 'info_m', 'Information');

        updateAcl($physicians_write, 'Physicians', 'pfeh', 'Information', 'info_m', 'Information', 'write');

        updateAcl($accounting_view, 'Accounting', 'pfeh', 'Information', 'info_m', 'Information', 'write');
        updateAcl($accounting_view, 'Accounting', 'pfeh', 'Fields Filter', 'field_filter', 'Fields Filter', 'write');
        updateAcl($accounting_view, 'Accounting', 'pfeh', 'Hook Filter', 'hooks', 'Hook Filter', 'write');
        updateAcl($accounting_view, 'Accounting', 'pfeh', 'Parameters', 'params', 'Parameters', 'write');
    },
    '0.2.1' => function () {
        $physicians_write = getAclIdNumber('Physicians', 'write');
        $accounting_view  = getAclIdNumber('Accounting', 'view');

        addObjectAcl('pfeh', 'Cards', 'cards', 'Cards');
        updateAcl($physicians_write, 'Physicians', 'pfeh', 'Cards', 'cards', 'Cards', 'write');
        updateAcl($accounting_view, 'Accounting', 'pfeh', 'Cards', 'cards', 'Cards', 'write');
    },
//    '0.2.2' => function () {
//
//    }
);
