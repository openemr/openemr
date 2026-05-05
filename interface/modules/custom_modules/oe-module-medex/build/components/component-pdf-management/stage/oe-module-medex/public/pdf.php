<?php
/**
 * MedEx PDF Form Filler - Iframe Wrapper
 *
 * Embeds medexbank.com PDF filling interface
 * Follows same pattern as hub.php - iframe + postMessage bridge
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2025 MedEx
 * @license   Proprietary - All Rights Reserved
 */

require_once(__DIR__ . '/../../../../globals.php');
require_once(__DIR__ . '/../src/MedExAPI.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

// Verify user is authenticated
if (!isset($_SESSION['authUserID'])) {
    die('Access denied. Please log in to OpenEMR.');
}

$entitlementApi = new \OpenEMR\Modules\MedEx\MedExAPI();
if (!$entitlementApi->hasServiceEntitlement('pdf_management')) {
    die('PDF Management service is not enabled. Please subscribe in MedEx Admin Dashboard.');
}

// Get MedEx credentials from current globals
$practiceId = $GLOBALS['medex_practice_id'] ?? '';
$apiKey = $GLOBALS['medex_api_key'] ?? '';

error_log('[MedEx PDF] From globals - practiceId: ' . ($practiceId ?: 'EMPTY') . ', apiKey: ' . (strlen($apiKey) > 0 ? 'SET ('.strlen($apiKey).' chars)' : 'EMPTY'));

// Fall back to database if globals not set
if (empty($practiceId) || empty($apiKey)) {
    $prefs = sqlQuery("SELECT MedEx_id, ME_api_key FROM medex_prefs WHERE ME_username IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1");
    error_log('[MedEx PDF] From database - result: ' . print_r($prefs, true));
    if (!empty($prefs['MedEx_id'])) {
        $practiceId = (string)$prefs['MedEx_id'];
    }
    if (!empty($prefs['ME_api_key'])) {
        $apiKey = (string)$prefs['ME_api_key'];
    }
}

error_log('[MedEx PDF] Final values - practiceId: ' . ($practiceId ?: 'EMPTY') . ', apiKey: ' . (strlen($apiKey) > 0 ? 'SET ('.strlen($apiKey).' chars)' : 'EMPTY'));

if (!$practiceId || !$apiKey) {
    die('MedEx not configured. Please configure MedEx in <a href="../admin/settings.php">Settings</a>.');
}

// Get patient and encounter context
$pid = $_GET['pid'] ?? $_SESSION['pid'] ?? null;
$encounter = $_GET['encounter'] ?? $_SESSION['encounter'] ?? null;

// Fetch patient data for display banner if PID available
$patientDisplay = null;
if ($pid) {
    $patientQuery = sqlQuery("SELECT fname, lname, DOB FROM patient_data WHERE pid = ?", array($pid));
    if ($patientQuery) {
        $patientDisplay = json_encode(array(
            'fname' => $patientQuery['fname'] ?? '',
            'lname' => $patientQuery['lname'] ?? '',
            'DOB' => $patientQuery['DOB'] ?? ''
        ));
    }
}

// Build iframe URL to MedExBank PDF module
// MedExConfig::baseUrl() already includes /cart/upload — do NOT append it again.
require_once __DIR__ . '/../src/MedExConfig.php';
$medexBaseUrl = \OpenEMR\Modules\MedEx\MedExConfig::baseUrl();
$medexUrl = rtrim((string)$medexBaseUrl, '/') . '/';

// Normalize docker hostname for browser access.
$medexUrl = str_replace('host.docker.internal', 'localhost', (string)$medexUrl);
if (!preg_match('#^https?://#i', $medexUrl)) {
    $medexUrl = 'https://' . ltrim($medexUrl, '/');
}

// If OpenEMR is served over https, do not embed http localhost (mixed content).
// BEGIN AI-EDITED CODE (GitHub Copilot, GPT-5.2): robust HTTPS detection for proxied/non-443 ports.
$forwardedProto = (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
$forwardedProto = strtolower(trim(explode(',', $forwardedProto)[0] ?? ''));
$openemrIsHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && $_SERVER['HTTPS'] !== '0')
    || (!empty($_SERVER['HTTP_SCHEME']) && $_SERVER['HTTP_SCHEME'] === 'https')
    || (!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https')
    || ($forwardedProto === 'https')
    || (($_SERVER['SERVER_PORT'] ?? '') === '443')
);
// END AI-EDITED CODE (GitHub Copilot, GPT-5.2)
if ($openemrIsHttps && str_starts_with($medexUrl, 'http://localhost')) {
    $medexUrl = 'https://' . substr($medexUrl, strlen('http://'));
}

$medexUrl = rtrim($medexUrl, '/') . '/';

// Login to MedEx to get a session token (secure session-based auth)
$medexApi = new \OpenEMR\Modules\MedEx\MedExAPI();

try {
    $medexApi->login();
    $sessionToken = $medexApi->getSessionToken();
    error_log('[MedEx PDF] Login successful, got session token');
} catch (\Exception $e) {
    error_log('[MedEx PDF] Login failed: ' . $e->getMessage());
    echo '<html><body>';
    echo '<h3>MedEx PDF Authentication Error</h3>';
    echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>Please check your configuration in <a href="../admin/settings.php">Settings</a>.</p>';
    echo '</body></html>';
    exit;
}

$iframeUrl = $medexUrl . 'index.php?route=pdf/filler';

// Determine trusted origin for postMessage.
$parsed = parse_url($medexUrl);
$medexOrigin = '';
if (!empty($parsed['scheme']) && !empty($parsed['host'])) {
    $medexOrigin = $parsed['scheme'] . '://' . $parsed['host'];
    if (!empty($parsed['port'])) {
        $medexOrigin .= ':' . $parsed['port'];
    }
}
// END AI-EDITED CODE (GitHub Copilot, GPT-5.2)

$iframeUrl .= '&token=' . urlencode($sessionToken);
$iframeUrl .= '&customer_id=' . urlencode($practiceId);

error_log('[MedEx PDF] Building iframe URL with token: ***hidden***, customer_id: ' . $practiceId);

// Pass patient context if available
if ($pid) {
    $iframeUrl .= '&pid=' . urlencode($pid);
}
if ($encounter) {
    $iframeUrl .= '&encounter=' . urlencode($encounter);
}

// Pass OpenEMR API endpoint and key for patient data fetching
// BEGIN AI-EDITED CODE (GitHub Copilot, GPT-5.2): build OpenEMR callback URL using OpenEMR host/webroot + site.
$siteId = $_SESSION['site_id'] ?? ($_GET['site'] ?? 'default');
$webroot = $GLOBALS['webroot'] ?? '';

// If MedEx runs in a separate container, it may need a docker-network URL to reach OpenEMR.
$openemrContainerUrl = $GLOBALS['medex_openemr_container'] ?? '';
if (!empty($openemrContainerUrl)) {
    $openemrBase = rtrim((string)$openemrContainerUrl, '/');
} else {
    $scheme = $openemrIsHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $openemrBase = $scheme . '://' . $host;
}

$openemrApiUrl = $openemrBase . $webroot . '/interface/modules/custom_modules/oe-module-medex/admin/pdf/api_patient_data.php?site=' . urlencode((string)$siteId);
// END AI-EDITED CODE (GitHub Copilot, GPT-5.2)
$iframeUrl .= '&openemr_api=' . urlencode($openemrApiUrl);
$iframeUrl .= '&api_key=' . urlencode($apiKey);

// Pass patient display data for banner
if ($patientDisplay) {
    $iframeUrl .= '&patient_display=' . urlencode(base64_encode($patientDisplay));
}

$csrfToken = CsrfUtils::collectCsrfToken();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('MedEx PDF Form Filler'); ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: Arial, sans-serif;
            background: #f5f7fa;
        }
        
        #medex-pdf-iframe {
            width: 100%;
            height: 100vh;
            border: none;
        }
        
        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            font-size: 18px;
            color: #666;
        }
        
        .loading .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e2e8f0;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loading" id="loading">
        <div class="spinner"></div>
        <p><?php echo xlt('Loading PDF Form Filler...'); ?></p>
    </div>
    
    <iframe 
        id="medex-pdf-iframe" 
        src="<?php echo htmlspecialchars($iframeUrl); ?>"
        style="display:none;"
        sandbox="allow-same-origin allow-scripts allow-forms allow-popups allow-downloads"
    ></iframe>
    
    <script>
    (function() {
        var iframe = document.getElementById('medex-pdf-iframe');
        var loading = document.getElementById('loading');

        // Add timeout to show error if iframe never loads
        var loadTimeout = setTimeout(function() {
            if (iframe.style.display === 'none') {
                loading.innerHTML = '<div class="spinner"></div>' +
                    '<p style="color: #e53e3e; font-weight: bold;">Failed to load PDF Form Filler</p>' +
                    '<p style="font-size: 14px;">The MedEx server may be unavailable or credentials may be invalid.</p>' +
                    '<p style="font-size: 12px; color: #718096;">Check browser console for details. URL: <?php echo htmlspecialchars(substr($iframeUrl, 0, 80)); ?>...</p>' +
                    '<button onclick="location.reload()" style="margin-top: 10px; padding: 8px 16px; background: #667eea; color: white; border: none; border-radius: 6px; cursor: pointer;">Retry</button>';
                console.error('[MedEx PDF] Iframe failed to load within 10 seconds');
                console.error('[MedEx PDF] URL:', '<?php echo htmlspecialchars($iframeUrl); ?>');
            }
        }, 10000);

        // Show iframe when loaded
        iframe.onload = function() {
            clearTimeout(loadTimeout);
            loading.style.display = 'none';
            iframe.style.display = 'block';
            console.log('[MedEx PDF] Iframe loaded successfully');
        };
        
        // PostMessage communication with MedExBank
        window.addEventListener('message', function(event) {
            // Verify origin
            // BEGIN AI-EDITED CODE (GitHub Copilot, GPT-5.2): match origin to configured MedEx URL.
            var trustedOrigin = <?php echo json_encode($medexOrigin ?: 'https://medexbank.com'); ?>;
            if (trustedOrigin && event.origin !== trustedOrigin) {
            // END AI-EDITED CODE (GitHub Copilot, GPT-5.2)
                console.warn('[MedEx PDF] Ignored message from:', event.origin);
                return;
            }
            
            console.log('[MedEx PDF] Received:', event.data);
            
            switch (event.data.type) {
                case 'REQUEST_PATIENT_DATA':
                    fetchPatientData(event.data.pid || <?php echo json_encode($pid); ?>);
                    break;
                    
                case 'SAVE_PDF_TO_CHART':
                    savePdfToChart(event.data);
                    break;
                    
                case 'PDF_READY':
                    console.log('[MedEx PDF] PDF ready for download');
                    break;
            }
        });
        
        // Fetch patient data from OpenEMR and send to iframe
        function fetchPatientData(pid) {
            if (!pid) {
                console.warn('[MedEx PDF] No patient ID');
                return;
            }
            if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
            fetch('/interface/modules/custom_modules/oe-module-medex/public/api/pdf_data.php', { // AI-EDITED (GitHub Copilot, GPT-5.2)
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'csrf_token=<?php echo urlencode($csrfToken); ?>&action=get_patient_data&pid=' + pid
                    + '&encounter=<?php echo urlencode($encounter ?? ''); ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    iframe.contentWindow.postMessage({
                        type: 'PATIENT_DATA',
                        patient: data.patient,
                        provider: data.provider,
                        encounter: data.encounter,
                        facility: data.facility
                    }, '*');
                }
            })
            .catch(err => console.error('[MedEx PDF] Fetch error:', err));
        }
        
        // Save filled PDF to patient chart
        function savePdfToChart(data) {
            if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
            fetch('/interface/modules/custom_modules/oe-module-medex/public/api/pdf_data.php', { // AI-EDITED (GitHub Copilot, GPT-5.2)
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'csrf_token=<?php echo urlencode($csrfToken); ?>&action=save_pdf'
                    + '&pid=' + encodeURIComponent(data.pid)
                    + '&encounter=' + encodeURIComponent(data.encounter || '')
                    + '&filename=' + encodeURIComponent(data.filename)
                    + '&pdf_base64=' + encodeURIComponent(data.pdf_base64)
                    + '&category=' + encodeURIComponent(data.category || 'Forms')
            })
            .then(response => response.json())
            .then(result => {
                iframe.contentWindow.postMessage({
                    type: 'PDF_SAVED',
                    success: result.success,
                    document_id: result.document_id,
                    error: result.error
                }, '*');
            })
            .catch(err => {
                iframe.contentWindow.postMessage({
                    type: 'PDF_SAVED',
                    success: false,
                    error: err.message
                }, '*');
            });
        }
        
        // Auto-fetch patient data if we have a pid
        <?php if ($pid): ?>
        setTimeout(function() {
            fetchPatientData(<?php echo json_encode($pid); ?>);
        }, 1000);
        <?php endif; ?>
    })();
    </script>
</body>
</html>
