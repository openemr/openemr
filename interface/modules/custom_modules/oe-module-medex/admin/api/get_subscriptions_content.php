<?php
/**
 * Get Subscriptions Tab Content
 *
 * Returns iframe to embed the existing subscriptions page
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
    echo xlt('MedEx is not configured yet. Please complete the registration process to manage subscriptions.');
    echo '</p>';
    echo '<a href="../admin/splash.php" style="padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">';
    echo '<i class="fa fa-rocket"></i> ' . xlt('Get Started');
    echo '</a>';
    echo '</div>';
    exit;
}

// Embed the subscriptions page with seamless styling
$webroot = $GLOBALS['webroot'] ?? '';
$siteId = $_SESSION['site_id'] ?? 'default';
$iframeSrc = $webroot . '/interface/modules/custom_modules/oe-module-medex/admin/subscriptions.php?site=' . urlencode($siteId);
?>
<style>
    .subscriptions-iframe-wrapper {
        width: 100%;
        height: calc(100vh - 200px);
        min-height: 800px;
        position: relative;
    }

    #subscriptions-iframe {
        width: 100%;
        height: 100%;
        border: none;
        display: block;
    }
</style>
<div class="subscriptions-iframe-wrapper">
    <iframe id="subscriptions-iframe" src="<?php echo attr($iframeSrc); ?>"></iframe>
</div>

<script>
// Define handler in window scope so it's accessible when iframe loads
window.handleSubscriptionsIframeLoad = function(iframe) {
    console.log('[Subscriptions] Iframe loaded, src:', iframe.src);

    try {
        // Mark as loaded for smooth fade-in
        iframe.classList.add('loaded');

        // Try to adjust height dynamically
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        if (iframeDoc && iframeDoc.body) {
            console.log('[Subscriptions] Can access iframe content, body height:', iframeDoc.body.scrollHeight);
            // Set initial height
            const height = Math.max(iframeDoc.body.scrollHeight, 800);
            iframe.style.height = height + 'px';

            // Watch for content changes
            const resizeObserver = new ResizeObserver(() => {
                iframe.style.height = iframeDoc.body.scrollHeight + 'px';
            });
            resizeObserver.observe(iframeDoc.body);
        } else {
            console.log('[Subscriptions] Cannot access iframe content');
        }
    } catch(e) {
        // Cross-origin - use viewport height
        iframe.classList.add('loaded');
        console.error('[Subscriptions] Error accessing iframe:', e.message);
        console.log('[Subscriptions] Using viewport height');
    }
};

// Add load and error handlers for iframe - execute immediately since content is loaded via AJAX
(function() {
    const iframe = document.getElementById('subscriptions-iframe');
    if (iframe) {
        console.log('[Subscriptions] Found iframe element:', iframe.src);

        // Bind onload handler
        iframe.onload = function() {
            console.log('[Subscriptions] Iframe onload fired');
            window.handleSubscriptionsIframeLoad(iframe);
        };

        // Bind onerror handler
        iframe.onerror = function(e) {
            console.error('[Subscriptions] Iframe failed to load:', e);
        };

        // Check if iframe is already loaded
        if (iframe.contentDocument && iframe.contentDocument.readyState === 'complete') {
            console.log('[Subscriptions] Iframe already loaded, calling handler immediately');
            window.handleSubscriptionsIframeLoad(iframe);
        } else {
            console.log('[Subscriptions] Waiting for iframe to load...');
        }
    } else {
        console.error('[Subscriptions] Could not find iframe element');
    }
})();

// Listen for messages from iframe
window.addEventListener('message', function(event) {
    if (event.data.type === 'subscription_updated') {
        // Reload overview
        if (window.loadOverview) {
            window.loadOverview();
        }
        // Show success toast
        if (window.showToast) {
            window.showToast('Subscription updated successfully', 'success');
        }
    }
});
</script>
