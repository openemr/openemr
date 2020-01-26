<?php
// Ensure this script is not called separately
if ((empty($_SESSION['lang_module_unique_id'])) ||
    (empty($unique_id)) ||
    ($unique_id != $_SESSION['lang_module_unique_id'])) {
    die('Authentication Error');
}
unset($_SESSION['lang_module_unique_id']);

use OpenEMR\Common\Acl\AclExtended;

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

return $ACL_UPGRADE = array(

    '0.2.0' => function () {
        $physicians_write = AclExtended::getAclIdNumber('Physicians', 'write');
        $accounting_view  = AclExtended::getAclIdNumber('Accounting', 'view');
        AclExtended::addObjectAcl('pfeh', 'Information', 'info_m', 'Information');

        AclExtended::updateAcl($physicians_write, 'Physicians', 'pfeh', 'Information', 'info_m', 'Information', 'write');

        AclExtended::updateAcl($accounting_view, 'Accounting', 'pfeh', 'Information', 'info_m', 'Information', 'write');
        AclExtended::updateAcl($accounting_view, 'Accounting', 'pfeh', 'Fields Filter', 'field_filter', 'Fields Filter', 'write');
        AclExtended::updateAcl($accounting_view, 'Accounting', 'pfeh', 'Hook Filter', 'hooks', 'Hook Filter', 'write');
        AclExtended::updateAcl($accounting_view, 'Accounting', 'pfeh', 'Parameters', 'params', 'Parameters', 'write');
    },
    '0.2.1' => function () {
        $physicians_write = AclExtended::getAclIdNumber('Physicians', 'write');
        $accounting_view  = AclExtended::getAclIdNumber('Accounting', 'view');

        AclExtended::addObjectAcl('pfeh', 'Cards', 'cards', 'Cards');
        AclExtended::updateAcl($physicians_write, 'Physicians', 'pfeh', 'Cards', 'cards', 'Cards', 'write');
        AclExtended::updateAcl($accounting_view, 'Accounting', 'pfeh', 'Cards', 'cards', 'Cards', 'write');
    },
);
