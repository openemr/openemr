<?php
/**
 * WhatsApp Settings Configuration Page
 * Provides admin interface for configuring WhatsApp messaging through MedEx
 * 
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx
 * @copyright Copyright (c) 2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

// Ensure this is accessed from within OpenEMR
if (!isset($GLOBALS['kernel'])) {
    die('Direct access not allowed');
}

// Require authentication
require_once(__DIR__ . '/../../../_parse_document.php');

use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Core\Header;

// Check admin permission
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo "<div class='alert alert-danger'>" . xlt('Access Denied. Admin privileges required.') . "</div>";
    exit;
}

// Get MedEx base URL
$medex_base_url = $GLOBALS['medex_base_url'] ?? 'http://localhost';

// Get configured WhatsApp phone number from MedEx
$whatsapp_phone = '';
$whatsapp_configured = false;

try {
    // Try to get WhatsApp status from medex_prefs
    $prefs = sqlQuery("SELECT status FROM medex_prefs LIMIT 1");
    if (!empty($prefs['status'])) {
        $status = json_decode($prefs['status'], true);
        if (!empty($status['whatsapp_phone'])) {
            $whatsapp_phone = $status['whatsapp_phone'];
            $whatsapp_configured = true;
        }
    }
} catch (\Throwable $e) {
    // Fail gracefully - just show unconfigured state
}

?>
<?php Header::setupHeader(''); ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-comments"></i> WhatsApp Settings
                    </h3>
                </div>
                <div class="card-body">
                    <?php if ($whatsapp_configured): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <strong>WhatsApp is Configured</strong>
                        <p class="mb-0 mt-2">WhatsApp messaging is enabled for your practice. Phone: <code><?php echo attr($whatsapp_phone); ?></code></p>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> <strong>WhatsApp Not Yet Configured</strong>
                        <p class="mb-0 mt-2">WhatsApp messaging is available but requires configuration in the MedEx platform.</p>
                    </div>
                    <?php endif; ?>

                    <h5 class="mt-4 mb-3">Configuration Steps</h5>
                    <ol>
                        <li>
                            <strong>Open MedEx Platform</strong>
                            <p class="text-muted small">Admin → System → WhatsApp Settings in your MedEx dashboard.</p>
                        </li>
                        <li>
                            <strong>Enter WhatsApp Phone Number</strong>
                            <p class="text-muted small">Use E.164 format (e.g., +15551234567)</p>
                        </li>
                        <li>
                            <strong>Test Connection</strong>
                            <p class="text-muted small">Click "Test Connection" to verify Plivo credentials are valid.</p>
                        </li>
                        <li>
                            <strong>Send Test Message</strong>
                            <p class="text-muted small">Send a test WhatsApp message to verify delivery works.</p>
                        </li>
                    </ol>

                    <div class="mt-4 pt-3 border-top">
                        <h5 class="mb-3">What You Can Do With WhatsApp</h5>
                        <ul class="list-group">
                            <li class="list-group-item">
                                <i class="fas fa-bell text-primary"></i> Send appointment reminders and recalls
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-comments text-primary"></i> Enable two-way messaging for patient responses
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-robot text-primary"></i> Integrate with AI Rescheduler for automated appointment management
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-lock text-primary"></i> HIPAA-compliant secure communication
                            </li>
                        </ul>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <h5 class="mb-3">Next Steps</h5>
                        <a href="<?php echo attr($medex_base_url); ?>/cart/upload/admin/?route=setting/whatsapp" 
                           target="_blank" class="btn btn-primary" style="white-space: nowrap;">
                            <i class="fas fa-external-link-alt"></i> Open MedEx WhatsApp Settings
                        </a>
                        <a href="javascript:history.back()" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>

                    <div class="mt-4 pt-3 border-top small text-muted">
                        <p class="mb-0">
                            <strong>Note:</strong> WhatsApp settings are configured in your MedEx platform account. 
                            This page provides quick access to configuration documentation and status.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once(__DIR__ . '/../../../_parse_document_footer.php'); ?>
