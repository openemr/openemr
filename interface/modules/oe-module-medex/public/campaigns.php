<?php
/**
 * MedEx Campaigns Manager
 *
 * Embeds the MedEx hipaabank.net campaign management UI (create/edit/delete)
 * in an SSO-authenticated iframe. Mirrors the pattern used by telehealth.php.
 *
 * Query params:
 *   type    - Campaign type slug: reminder, recall, gogreen, announce, clinical, survey
 *   site    - OpenEMR site ID (defaults to 'default')
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;

// Must be admin
if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo '<div class="alert alert-danger">' . xlt('Access denied') . '</div>';
    exit;
}

require_once(__DIR__ . '/../src/MedExConfig.php');
require_once(__DIR__ . '/../src/MedExAPI.php');
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

if (!$api->isConfigured()) {
    echo '<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> ' . xlt('MedEx is not configured.') . '</div>';
    exit;
}

if (!$api->hasAnyServiceEntitlement(['appointment_reminders', 'medex_messages'])) {
    echo '<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> '
        . xlt('Appointment reminders service is not enabled. Please subscribe in MedEx Admin Dashboard.')
        . '</div>';
    exit;
}

// Map type slug → hipaabank.net g= parameter and display label
$typeMap = [
    'reminder' => ['g' => 'rem', 'label' => 'Appointment Reminders',  'icon' => 'fa-bell'],
    'recall'   => ['g' => 'rec', 'label' => 'Recalls',                 'icon' => 'fa-calendar-plus'],
    'gogreen'  => ['g' => '',    'label' => 'GoGreen Campaigns',        'icon' => 'fa-leaf'],
    'announce' => ['g' => '',    'label' => 'Announcements',            'icon' => 'fa-bullhorn'],
    'clinical' => ['g' => '',    'label' => 'Clinical Reminders',       'icon' => 'fa-stethoscope'],
    'survey'   => ['g' => '',    'label' => 'Surveys',                  'icon' => 'fa-clipboard-list'],
];

$type     = $_GET['type'] ?? 'reminder';
$typeMeta = $typeMap[$type] ?? $typeMap['reminder'];
$label    = $typeMeta['label'];
$icon     = $typeMeta['icon'];

// Authenticate and get session token
$sessionToken = null;
$practiceId   = null;
try {
    $loginData    = $api->login(false);
    $sessionToken = $api->getSessionToken();
    $practiceId   = $loginData['customer_id'] ?? ($loginData['practice_id'] ?? null);
} catch (\Exception $e) {
    ?>
    <div class="alert alert-danger">
        <h4><i class="fa fa-exclamation-circle"></i> <?php echo xlt('MedEx Authentication Error'); ?></h4>
        <p><?php echo text($e->getMessage()); ?></p>
    </div>
    <?php
    exit;
}

// Build the iframe URL — must use the *public* HTTPS endpoint, not the internal k8s service name.
$medexUrl = \OpenEMR\Modules\MedEx\MedExConfig::publicBaseUrl() . '/';
$iframeUrl  = $medexUrl . 'index.php?route=information/campaigns';
if (!empty($typeMeta['g'])) {
    $iframeUrl .= '&g=' . urlencode($typeMeta['g']);
}
$iframeUrl .= '&token=' . urlencode($sessionToken);
if ($practiceId) {
    $iframeUrl .= '&customer_id=' . urlencode($practiceId);
}

// Trusted origin for postMessage
$parsed     = parse_url($medexUrl);
$medexOrigin = '';
if (!empty($parsed['scheme']) && !empty($parsed['host'])) {
    $medexOrigin = $parsed['scheme'] . '://' . $parsed['host'];
    if (!empty($parsed['port'])) {
        $medexOrigin .= ':' . $parsed['port'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xlt('MedEx') . ' — ' . xlt($label); ?></title>
    <?php Header::setupHeader(['common']); ?>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            background: #f5f5f5;
        }
        #campaigns-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 16px;
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            height: 44px;
            box-sizing: border-box;
        }
        #campaigns-header h1 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            color: #333;
        }
        #campaigns-header h1 i {
            color: #667eea;
            margin-right: 6px;
        }
        .btn-close-campaigns {
            background: #6c757d;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 4px 12px;
            font-size: 13px;
            cursor: pointer;
        }
        .btn-close-campaigns:hover { background: #5a6268; }
        #campaigns-iframe {
            width: 100%;
            border: none;
            display: block;
            /* Full height minus header */
            height: calc(100vh - 44px);
        }
        #loading-overlay {
            position: absolute;
            top: 44px;
            left: 0;
            right: 0;
            bottom: 0;
            background: #f5f5f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            z-index: 10;
        }
        .loading-spinner {
            width: 36px;
            height: 36px;
            border: 3px solid #dee2e6;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div id="campaigns-header">
    <h1>
        <i class="fa <?php echo attr($icon); ?>"></i>
        <?php echo xlt('MedEx') . ' — ' . xlt($label); ?>
    </h1>
    <button class="btn-close-campaigns" onclick="closeCampaigns()">
        <i class="fa fa-times"></i> <?php echo xlt('Close'); ?>
    </button>
</div>

<div id="loading-overlay">
    <div class="loading-spinner"></div>
    <div style="color:#666; font-size:14px;"><?php echo xlt('Loading campaigns...'); ?></div>
</div>

<iframe
    id="campaigns-iframe"
    src="<?php echo attr($iframeUrl); ?>"
    allow="camera; microphone"
    sandbox="allow-same-origin allow-scripts allow-forms allow-popups allow-popups-to-escape-sandbox"
    onload="document.getElementById('loading-overlay').style.display='none';"
    onerror="document.getElementById('loading-overlay').innerHTML='<div class=\'alert alert-danger\'><?php echo xlt('Failed to load campaigns. Please try again.'); ?></div>';"
></iframe>

<script>
window.closeCampaigns = function closeCampaigns() {
    console.log('[campaigns.php] closeCampaigns called');
    console.log('[campaigns.php] window.opener:', window.opener);
    console.log('[campaigns.php] window.parent !== window:', window.parent !== window);

    // If opened in a modal overlay (window.opener or parent frame), signal close
    if (window.opener && !window.opener.closed) {
        console.log('[campaigns.php] Closing via window.close()');
        window.close();
    } else if (window.parent !== window) {
        console.log('[campaigns.php] Posting message to parent:', {action: 'closeCampaignsModal'});
        window.parent.postMessage({action: 'closeCampaignsModal'}, '*');
    } else {
        console.log('[campaigns.php] Going back in history');
        window.history.back();
    }
};

// Listen for postMessage from hipaabank.net iframe (e.g. save/delete events)
window.addEventListener('message', function(event) {
    <?php if ($medexOrigin): ?>
    if (event.origin !== <?php echo json_encode($medexOrigin); ?>) return;
    <?php endif; ?>
    const data = event.data;
    if (!data) return;
    // Forward to parent so the admin dashboard can refresh campaign lists
    if (window.parent !== window) {
        window.parent.postMessage({action: 'campaignUpdated', campaignType: <?php echo json_encode($type); ?>, payload: data}, '*');
    }
});
</script>

</body>
</html>
