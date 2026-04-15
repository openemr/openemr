<?php
/**
 * Module Configuration File
 * Required by OpenEMR to register the gear icon in Module Manager.
 *
 * MedEx no longer uses the legacy local admin dashboard as its primary entry
 * point from Module Manager. The gear should open the same setup/onboarding
 * modal used by the help/install flow.
 */

$module_config = 1;

if (basename($_SERVER['SCRIPT_FILENAME']) === 'moduleConfig.php') {
    if (empty($_GET['site'])) {
        $_GET['site'] = 'default';
    }

    require_once(__DIR__ . '/../../../globals.php');
    require_once(__DIR__ . '/../../../../src/Common/Acl/AclMain.php');

    // Hard-stop access for non-admin users even if this URL is hit directly.
    if (!\OpenEMR\Common\Acl\AclMain::aclCheckCore('admin', 'super')) {
        http_response_code(403);
        echo 'Access denied';
        exit;
    }

    $site = $_GET['site'] ?? 'default';
    $webroot = $GLOBALS['webroot'] ?? '';
    $setupUrl = $webroot . '/interface/modules/custom_modules/oe-module-medex/show_help_setup.php?site=' . urlencode((string)$site);
    ?><!DOCTYPE html><html><head><meta charset="utf-8"></head><body>
    <script>
    (function() {
        var target = <?php echo json_encode($setupUrl); ?>;
        try {
            if (window.top && typeof window.top.restoreSession === 'function') {
                window.top.restoreSession();
            }
        } catch (e) {}
        try {
            if (window.top && typeof window.top.openModuleHelp === 'function') {
                window.top.openModuleHelp(target, 'MedEx Setup Help');
                return;
            }
        } catch (e) {
            // Ignore and keep falling through.
        }
        try {
            if (window.top && window.top.location) {
                window.top.location.href = target;
            } else {
                window.location.href = target;
            }
            return;
        } catch (e) {}
        window.location.href = target;
    })();
    </script>
    </body></html><?php
    exit;
}
