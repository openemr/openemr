<?php
/**
 * PDF Template Manager - OpenEMR SaaS Wrapper
 * 
 * Authenticates user and loads MedExBank PDF interface via iframe
 * SaaS model: All PDF functionality hosted on medexbank.com
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . '/../../../../../globals.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

// Verify user is authenticated
if (!isset($_SESSION['authUserID'])) {
    die('Access denied. Please log in to OpenEMR.');
}

// Get customer_id from OpenEMR globals (same as practice_id)
$customerId = $GLOBALS['medex_practice_id'] ?? '';
$apiToken = $GLOBALS['medex_api_key'] ?? '';

// Fall back to database if globals not set
if (empty($customerId) || empty($apiToken)) {
    $prefs = sqlQuery("SELECT MedEx_id, ME_api_key FROM medex_prefs WHERE ME_username IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1");
    if (!empty($prefs['MedEx_id'])) {
        $customerId = (string)$prefs['MedEx_id'];
    }
    if (!empty($prefs['ME_api_key'])) {
        $apiToken = (string)$prefs['ME_api_key'];
    }
}

if (!$customerId || !$apiToken) {
    die('MedEx not configured. Please configure MedEx in <a href="../settings.php">Settings</a>.');
}

// Get MedEx server URL from global config (for local dev) or default to production
// medex_bank_url is like http://medex-localhost-80-app-1.orb.local/cart/upload
// We need the base URL without the /cart/upload path for the /pdf endpoint
$medexBankUrl = $GLOBALS['medex_bank_url'] ?? 'https://medexbank.com/cart/upload';
$parsedUrl = parse_url($medexBankUrl);
$medexServerUrl = ($parsedUrl['scheme'] ?? 'https') . '://' . ($parsedUrl['host'] ?? 'medexbank.com');
if (!empty($parsedUrl['port'])) {
    $medexServerUrl .= ':' . $parsedUrl['port'];
}
$medexBaseUrl = $medexServerUrl . '/pdf';

// Determine which page to load (dashboard, editor, or marketplace)
$page = $_GET['page'] ?? 'index';
$allowedPages = ['index', 'editor', 'marketplace'];
if (!in_array($page, $allowedPages)) {
    $page = 'index';
}

// Build iframe URL with customer authentication
$iframeUrl = "{$medexBaseUrl}/{$page}.html?customer_id=" . urlencode($customerId);

// DEBUG - remove after testing
error_log("[PDF Manager] customerId={$customerId}, page={$page}, iframeUrl={$iframeUrl}");

// Pass template_id for editor
if ($page === 'editor' && !empty($_GET['template_id'])) {
    $iframeUrl .= '&template_id=' . urlencode($_GET['template_id']);
}

$csrfToken = CsrfUtils::collectCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Template Manager | MedEx</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        html, body {
            height: 100%;
            overflow: hidden;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        
        .nav-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 0.5rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
        
        .nav-bar h1 {
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }
        
        .nav-bar .nav-links {
            display: flex;
            gap: 0.5rem;
        }
        
        .nav-bar .nav-link {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        
        .nav-bar .nav-link:hover,
        .nav-bar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .nav-bar .nav-link i {
            margin-right: 0.4rem;
        }
        
        .iframe-container {
            flex: 1;
            overflow: hidden;
        }
        
        .iframe-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
            transition: opacity 0.3s;
        }
        
        .loading-overlay.hidden {
            opacity: 0;
            pointer-events: none;
        }
        
        .loading-content {
            text-align: center;
            color: white;
        }
        
        .loading-content .spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .customer-badge {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.25rem 0.6rem;
            border-radius: 4px;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Navigation Bar -->
        <nav class="nav-bar">
            <div class="d-flex align-items-center gap-3">
                <h1><i class="fas fa-file-pdf me-2"></i>PDF Templates</h1>
                <span class="customer-badge" title="<?php echo htmlspecialchars($iframeUrl); ?>">ID: <?php echo htmlspecialchars($customerId); ?></span>
            </div>
            <div class="nav-links">
                <a href="?page=index" class="nav-link <?php echo $page === 'index' ? 'active' : ''; ?>">
                    <i class="fas fa-th-large"></i>Dashboard
                </a>
                <a href="?page=editor" class="nav-link <?php echo $page === 'editor' ? 'active' : ''; ?>">
                    <i class="fas fa-plus"></i>Create
                </a>
                <a href="?page=marketplace" class="nav-link <?php echo $page === 'marketplace' ? 'active' : ''; ?>">
                    <i class="fas fa-store"></i>Marketplace
                </a>
            </div>
        </nav>
        
        <!-- Iframe Container -->
        <div class="iframe-container" style="position: relative;">
            <div class="loading-overlay" id="loading">
                <div class="loading-content">
                    <div class="spinner"></div>
                    <p>Loading PDF Manager...</p>
                </div>
            </div>
            <iframe 
                id="pdf-frame"
                src="<?php echo htmlspecialchars($iframeUrl); ?>"
                allow="fullscreen"
                onload="document.getElementById('loading').classList.add('hidden')">
            </iframe>
        </div>
    </div>
    
    <script>
        // Handle navigation within iframe
        window.addEventListener('message', function(event) {
            // Accept messages from the configured MedEx server (or local dev)
            const allowedOrigins = [
                '<?php echo htmlspecialchars($medexServerUrl); ?>'
            ];
            if (!allowedOrigins.includes(event.origin)) return;
            
            const data = event.data;
            if (data.action === 'navigate') {
                // Navigate to different page
                if (data.page === 'editor' && data.template_id) {
                    window.location.href = `?page=editor&template_id=${data.template_id}`;
                } else if (data.page) {
                    window.location.href = `?page=${data.page}`;
                }
            }
        });
        
        // Refresh iframe
        function refreshFrame() {
            const frame = document.getElementById('pdf-frame');
            const loading = document.getElementById('loading');
            loading.classList.remove('hidden');
            frame.src = frame.src;
        }
    </script>
</body>
</html>
