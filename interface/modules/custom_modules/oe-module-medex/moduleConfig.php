<?php
/**
 * Module Configuration File
 * Required by OpenEMR to register the gear icon in Module Manager.
 * Keep behavior simple: gear should always route to the MedEx onboarding/dashboard splash.
 */

$module_config = 1;

if (basename($_SERVER['SCRIPT_FILENAME']) === 'moduleConfig.php') {
    if (empty($_GET['site'])) {
        $_GET['site'] = 'default';
    }

    require_once(__DIR__ . '/../../../globals.php');

    $site = $_GET['site'] ?? 'default';
    $splashUrl = $GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-medex/admin/splash.php?minimal=1&site=' . urlencode((string)$site);
    ?><!DOCTYPE html><html><head></head><body>
    <script>
    (function() {
        try {
            var target = <?php echo json_encode($splashUrl); ?>;
            if (window.top && window.top.location) {
                window.top.location.href = target;
            } else {
                window.location.href = target;
            }
        } catch (e) {
            window.location.href = <?php echo json_encode($splashUrl); ?>;
        }
    })();
    </script>
    </body></html><?php
    exit;
}
