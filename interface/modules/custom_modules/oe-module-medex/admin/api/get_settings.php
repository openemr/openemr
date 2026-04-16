<?php
/**
 * Settings tab placeholder.
 * Legacy module-wide settings UI was removed from the active module path.
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;

if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo '<div class="overview-card"><p style="color:#dc3545;">' . xlt('Access denied') . '</p></div>';
    exit;
}

require_once(__DIR__ . '/../../src/MedExAPI.php');
$api = new \OpenEMR\Modules\MedEx\MedExAPI();
$siteId = (string)($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default'));

if (!$api->isConfigured()) {
    echo '<div class="overview-card">';
    echo '<h3><i class="fa fa-exclamation-circle"></i> ' . xlt('Setup Required') . '</h3>';
    echo '<p style="color:#666; margin-bottom:20px;">' . xlt('MedEx is not configured yet. Please complete the registration process to continue.') . '</p>';
    echo '<a href="splash.php?site=' . attr_url($siteId) . '" class="btn btn-primary"><i class="fa fa-rocket"></i> ' . xlt('Get Started') . '</a>';
    echo '</div>';
    exit;
}
?>
<div class="overview-card">
    <h3><i class="fa fa-cog"></i> <?php echo xlt('Settings'); ?></h3>
    <p style="color:#475569; margin:0 0 18px 0; max-width:720px;">
        <?php echo xlt('Legacy module-wide settings were removed from the MedEx module. Service configuration now happens during onboarding and within each subscribed service.'); ?>
    </p>
    <a
        href="#"
        class="btn btn-primary"
        onclick="if (window.parent && typeof window.parent.switchToTab === 'function') { return window.parent.switchToTab('subscriptions'); } if (typeof switchToTab === 'function') { return switchToTab('subscriptions'); } window.location.href='index.php?site=<?php echo attr_js($siteId); ?>&tab=subscriptions'; return false;"
    >
        <i class="fa fa-shopping-cart"></i> <?php echo xlt('Open Services'); ?>
    </a>
</div>
