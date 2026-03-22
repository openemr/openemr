<?php
/**
 * Get Settings Tab Content
 *
 * Returns iframe to embed the existing settings page with seamless UX
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo '<div class="overview-card"><p style="color: #dc3545;">' . xlt('Access denied') . '</p></div>';
    exit;
}

// Load MedEx API
require_once(__DIR__ . '/../../src/MedExAPI.php');
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

// Check if user is registered - if not, show setup message
if (!$api->isConfigured()) {
    echo '<div class="overview-card">';
    echo '<h3><i class="fa fa-exclamation-circle"></i> ' . xlt('Setup Required') . '</h3>';
    echo '<p style="color: #666; margin-bottom: 20px;">';
    echo xlt('MedEx is not configured yet. Please complete the registration process to access settings.');
    echo '</p>';
    echo '<a href="../admin/splash.php" style="padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">';
    echo '<i class="fa fa-rocket"></i> ' . xlt('Get Started');
    echo '</a>';
    echo '</div>';
    exit;
}

// Embed the settings page with seamless styling
$webroot = $GLOBALS['webroot'] ?? '';
$siteId = $_SESSION['site_id'] ?? 'default';
$iframeSrc = $webroot . '/interface/modules/custom_modules/oe-module-medex/admin/settings.php?site=' . urlencode($siteId);
?>
<style>
    .settings-iframe-wrapper {
        width: 100%;
        height: calc(100vh - 200px);
        min-height: 800px;
        position: relative;
    }

    #settings-iframe {
        width: 100%;
        height: 100%;
        border: none;
        display: block;
    }
</style>
<div class="settings-iframe-wrapper">
    <iframe id="settings-iframe" src="<?php echo attr($iframeSrc); ?>"></iframe>
</div>

<script>
window.handleSettingsIframeLoad = function(iframe) {
    try {
        // Mark as loaded for smooth fade-in
        iframe.classList.add('loaded');

        // Try to adjust height dynamically
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        if (iframeDoc && iframeDoc.body) {
            // Set initial height
            const height = Math.max(iframeDoc.body.scrollHeight, 800);
            iframe.style.height = height + 'px';

            // Watch for content changes
            const resizeObserver = new ResizeObserver(() => {
                iframe.style.height = iframeDoc.body.scrollHeight + 'px';
            });
            resizeObserver.observe(iframeDoc.body);
        }
    } catch(e) {
        // Cross-origin - use viewport height
        iframe.classList.add('loaded');
        console.log('[Settings] Using viewport height');
    }
};

// Bind handlers immediately
(function() {
    const iframe = document.getElementById('settings-iframe');
    if (iframe) {
        iframe.onload = function() {
            window.handleSettingsIframeLoad(iframe);
        };
    }
})();

// Listen for messages from iframe
window.addEventListener('message', function(event) {
    if (event.data.type === 'settings_updated' || event.data.type === 'settings_synced') {
        // Reload overview
        if (window.loadOverview) {
            window.loadOverview();
        }
        // Show success toast
        if (window.showToast) {
            const message = event.data.type === 'settings_synced'
                ? 'Settings saved and synced successfully'
                : 'Settings updated successfully';
            window.showToast(message, 'success');
        }
    }
});
</script>
