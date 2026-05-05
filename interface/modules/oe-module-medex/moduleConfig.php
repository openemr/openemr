<?php
/**
 * Module Configuration File
 * Required by OpenEMR to register the gear icon in Module Manager.
 * Keep behavior simple: gear always opens MedEx Admin Dashboard in an OpenEMR tab.
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
    $dashboardUrl = $webroot . '/interface/modules/custom_modules/oe-module-medex/admin/index.php?site=' . urlencode((string)$site);
    ?><!DOCTYPE html><html><head><meta charset="utf-8"></head><body>
    <script>
    (function() {
        var target = <?php echo json_encode($dashboardUrl); ?>;
        try {
            if (window.top && typeof window.top.restoreSession === 'function') {
                window.top.restoreSession();
            }
        } catch (e) {}
        try {
            // Preferred OpenEMR tabs API.
            if (window.top && typeof window.top.navigateTab === 'function') {
                window.top.navigateTab(target, 'medex_admin', function() {
                    try {
                        if (typeof window.top.activateTabByName === 'function') {
                            window.top.activateTabByName('medex_admin', true);
                        }
                    } catch (e) {}
                }, 'Loading MedEx');
                return;
            }
        } catch (e) {
            // Ignore and keep falling through.
        }
        try {
            // Fallback: open new browser tab if tabs API unavailable.
            window.open(target, '_blank');
            return;
        } catch (e) {}
        // Last fallback: navigate current frame/window.
        try {
            if (window.top && window.top.location) {
                window.top.location.href = target;
            } else {
                window.location.href = target;
            }
        } catch (e) {
            window.location.href = target;
        }
    })();
    </script>
    </body></html><?php
    exit;
}
