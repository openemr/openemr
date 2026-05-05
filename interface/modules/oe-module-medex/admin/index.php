<?php
/**
 * MedEx Admin Dashboard
 *
 * Unified admin interface for MedEx module management
 * Consolidates: Overview, Subscriptions, Settings, Backups, PDF Manager
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
// Default to 'default' for single-site installations
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo "<html><body>" . xlt('Access denied') . "</body></html>";
    exit;
}

// Load MedEx API
require_once(__DIR__ . '/../src/MedExAPI.php');
$api = null;
$isConfigured = false;
$isActive = false;
try {
    $api = new \OpenEMR\Modules\MedEx\MedExAPI();
    // Check if user is registered
    $isConfigured = $api->isConfigured();
    $isActive = $isConfigured && $api->isActive();
} catch (\Throwable $e) {
    error_log('[MedEx Admin] Failed to initialize MedExAPI in dashboard: ' . $e->getMessage());
}

// Check whether any services are actually subscribed (required to enable Sync)
// Use getEnabledServices() which handles its own caching and refreshes from the API when needed.
$hasActiveSubscriptions = false;
if ($isActive) {
    try {
        $enabledServices = $api->getEnabledServices();
        $hasActiveSubscriptions = !empty($enabledServices);
    } catch (\Throwable $e) {
        // Treat as no subscriptions on error
    }
}

// Get current tab from query parameter
$currentTab = $_GET['tab'] ?? 'overview';
$validTabs = ['overview', 'subscriptions', 'settings', 'backups'];
if (!in_array($currentTab, $validTabs)) {
    $currentTab = 'overview';
}

// Get CSRF token (compatible across OpenEMR versions)
try {
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
} catch (\Throwable $e) {
    $session = null;
}
if (!$session || empty($session->get('csrf_private_key', null))) {
    if ($session) {
        CsrfUtils::setupCsrfKey($session);
    } else {
        CsrfUtils::setupCsrfKey();
    }
}
$csrfToken = '';
try {
    if ($session instanceof \Symfony\Component\HttpFoundation\Session\SessionInterface) {
        $csrfToken = (string) CsrfUtils::collectCsrfToken(session: $session);
    } else {
        $csrfToken = (string) CsrfUtils::collectCsrfToken();
    }
} catch (\Throwable $e) {
    $csrfToken = (string) CsrfUtils::collectCsrfToken();
}
$siteId = $_SESSION['site_id'] ?? ($_GET['site'] ?? 'default');
$cloudOnly = !empty($_GET['cloud_only']);
$helpCenterUrl = ($GLOBALS['webroot'] ?? '')
    . '/interface/modules/custom_modules/oe-module-medex/admin/help_center.php?site=' . urlencode((string)$siteId);

// Cloud-hosted dashboard handoff (api.hipaabank.net) via SSO.
// Use ?local=1 to bypass and view the local OpenEMR-rendered dashboard.
if ($isConfigured && $isActive && empty($_GET['local'])) {
    try {
        // Do not force fresh credential login on every page load.
        // Reuse cached session token when valid to avoid unnecessary fallback to
        // the local legacy dashboard during transient credential drift.
        $loginData = $api->login(false);
        $sessionToken = (string)($loginData['token'] ?? '');
        $practiceId = (string)(
            $loginData['practice_id']
            ?? ($loginData['practice']['P_PID'] ?? '')
        );
        if ($practiceId === '') {
            $pref = sqlQuery("SELECT MedEx_id FROM medex_prefs WHERE MedEx_id IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1");
            $practiceId = (string)($pref['MedEx_id'] ?? '');
        }
        if ($sessionToken !== '' && $practiceId !== '') {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
            $webRoot = rtrim((string)($GLOBALS['webroot'] ?? ''), '/');
            $openEmrBaseUrl = ($host !== '') ? ($scheme . '://' . $host . $webRoot) : '';
            $ssoPayload = [
                'practice_id' => $practiceId,
                'session_token' => $sessionToken,
                'timestamp' => time(),
                'nonce' => bin2hex(random_bytes(16)),
                'source' => 'openemr_dashboard',
                'openemr_base_url' => $openEmrBaseUrl,
                'site' => (string)$siteId
            ];
            $ssoToken = base64_encode(json_encode($ssoPayload));
            $tab = (string)($_GET['tab'] ?? 'overview');
            $cloudUrl = 'https://api.hipaabank.net/cart/upload/dashboard_sso.php'
                . '?site=' . urlencode((string)$siteId)
                . '&tab=' . urlencode($tab)
                . '&sso_token=' . urlencode($ssoToken);
            header('Location: ' . $cloudUrl);
            exit;
        }
    } catch (\Throwable $e) {
        error_log('[MedEx Admin] Cloud dashboard handoff failed, falling back to local dashboard: ' . $e->getMessage());
    }
}

if ($cloudOnly) {
    http_response_code(502);
    echo '<!doctype html><html><head><meta charset="utf-8"><title>MedEx Admin</title></head><body style="margin:0;background:#f5f8fc;color:#0f172a;font-family:Segoe UI,Tahoma,sans-serif;">';
    echo '<div style="max-width:760px;margin:56px auto;padding:24px;background:#fff;border:1px solid #dbe5ee;border-radius:12px;">';
    echo '<h2 style="margin:0 0 12px 0;color:#0f4b8f;">Unable to load MedEx cloud dashboard</h2>';
    echo '<p style="margin:0 0 16px 0;">Please refresh this page. If the issue continues, reconnect MedEx in Account Settings.</p>';
    echo '<p style="margin:0;"><a href="' . attr(($GLOBALS['webroot'] ?? '') . '/interface/modules/custom_modules/oe-module-medex/admin/index.php?site=' . urlencode((string)$siteId) . '&tab=settings&local=1') . '" style="color:#0f4b8f;font-weight:600;">Open Account Settings</a></p>';
    echo '</div></body></html>';
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('MedEx Admin'); ?></title>
    <?php Header::setupHeader(['opener']); ?>
    <style>
        :root {
            --medex-primary: #0f4b8f;
            --medex-primary-dark: #0a3460;
            --medex-bg: #f4f7f6;
            --medex-surface: #ffffff;
            --medex-border: #dbe5ee;
            --medex-text: #0f172a;
            --medex-muted: #64748b;
            --medex-panel-bg: #f8fbff;
        }
        body {
            font-size: 14px;
            background: var(--medex-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--medex-text);
        }

        /* Dashboard Header */
        .dashboard-header {
            background: var(--medex-surface);
            color: var(--medex-text);
            border-bottom: 1px solid var(--medex-border);
            box-shadow: 0 8px 20px rgba(15, 75, 143, 0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .dashboard-header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 70px;
        }
        .tab-navigation-inner {
            display: flex;
            gap: 10px;
            align-items: center;
            min-height: 70px;
            flex-wrap: wrap;
            padding: 12px 0;
        }
        .dashboard-brand {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.2px;
            color: var(--medex-primary);
            margin-right: 6px;
        }
        .dashboard-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }
        .sync-status {
            font-size: 13px;
            color: var(--medex-text);
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: var(--medex-panel-bg);
            border: 1px solid var(--medex-border);
            border-radius: 6px;
        }
        .sync-button {
            background: var(--medex-primary);
            border: 1px solid var(--medex-primary);
            color: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sync-button:hover {
            background: var(--medex-primary-dark);
            border-color: var(--medex-primary-dark);
        }
        .sync-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .sync-button.syncing {
            background: var(--medex-primary-dark);
            border-color: var(--medex-primary-dark);
        }
        .embedded-close {
            display: none;
            background: #fff;
            color: var(--medex-primary);
            border: 1px solid #c8dbef;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }
        .embedded-close:hover {
            background: #f3f8fd;
        }
        body.in-config-modal .embedded-close {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .dashboard-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .btn-sync-header {
            background: #fff;
            color: #0f4b8f;
            border: 1px solid #dbe5ee;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-sync-header:hover:not(:disabled) {
            background: #f8fbff;
            border-color: #0f4b8f;
            box-shadow: 0 2px 4px rgba(15, 75, 143, 0.1);
        }
        .btn-sync-header:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .btn-sync-header.syncing i {
            animation: fa-spin 2s linear infinite;
        }
        .help-center-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 999px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            color: var(--medex-primary);
            background: #ecf4fb;
            border: 1px solid #c7dff3;
            transition: all 0.2s ease;
        }
        .help-center-link:hover {
            background: #fff;
            border-color: #d7e6f4;
            color: var(--medex-primary-dark);
            box-shadow: 0 8px 18px rgba(7, 39, 74, 0.14);
        }
        .header-toggle-switch {
            display: flex;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }
        .header-toggle-switch input[type="checkbox"] {
            position: relative;
            width: 48px;
            height: 24px;
            appearance: none;
            background: #cbd5e1;
            border: 1px solid #94a3b8;
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .header-toggle-switch input[type="checkbox"]:checked {
            background: #4ade80;
        }
        .header-toggle-switch input[type="checkbox"]::before {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: white;
            top: 2px;
            left: 2px;
            transition: left 0.3s;
        }
        .header-toggle-switch input[type="checkbox"]:checked::before {
            left: 26px;
        }
        .tab-link {
            padding: 10px 14px;
            color: var(--medex-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid transparent;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
        }
        .tab-link:hover {
            color: var(--medex-primary);
            background: #f0f6fc;
            border-color: #d6e5f4;
        }
        .tab-link.active {
            color: var(--medex-primary);
            background: #e8f1fb;
            border-color: #bfd8ef;
        }
        .tab-link.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
        .menu-anchor {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        .menu-caret {
            margin-left: 6px;
            font-size: 11px;
        }
        .menu-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            min-width: 260px;
            max-height: 360px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #dbe5ee;
            border-radius: 10px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.18);
            z-index: 1200;
            padding: 8px;
        }
        .menu-dropdown.open {
            display: block;
        }
        .menu-item {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            border: 0;
            background: transparent;
            color: var(--medex-text);
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            padding: 9px 10px;
            text-align: left;
            cursor: pointer;
        }
        .menu-item:hover {
            background: #eef6fd;
            color: var(--medex-primary);
        }
        .context-bar {
            max-width: 1400px;
            margin: 0 auto;
            padding: 8px 24px 10px;
            color: #425466;
            font-size: 13px;
            font-weight: 600;
        }
        .tab-link.disabled {
            color: #a8b0bd;
            cursor: not-allowed;
            pointer-events: none;
        }
        .tab-link.disabled:hover {
            background: transparent;
        }
        .tab-link i {
            font-size: 16px;
        }

        /* Main Container */
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 22px 24px 28px;
            background: var(--medex-bg);
            min-height: calc(100vh - 70px);
        }

        /* Tab Content */
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Unified Panel System (based on subscriptions.php) */
        .panel {
            background: var(--medex-surface);
            border: 1px solid var(--medex-border);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 10px 24px rgba(15, 75, 143, 0.07);
            margin-bottom: 20px;
        }
        .panel h3 {
            margin: 0 0 20px 0;
            font-size: 20px;
            font-weight: 600;
            color: var(--medex-text);
        }
        .panel-grid {
            display: grid;
            gap: 30px;
            margin-bottom: 30px;
        }
        .panel-grid.two-col {
            grid-template-columns: 2fr 1fr;
        }
        @media (max-width: 968px) {
            .panel-grid.two-col {
                grid-template-columns: 1fr;
            }
        }

        /* Card System */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        @media (max-width: 1200px) {
            .card-grid {
                grid-template-columns: 1fr;
            }
        }
        .card {
            border: 2px solid var(--medex-border);
            border-radius: 10px;
            padding: 20px;
            transition: all 0.2s ease;
            position: relative;
            background: var(--medex-surface);
        }
        .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .card.active {
            border-color: #9ac0e2;
            background: var(--medex-panel-bg);
        }
        .card.selected {
            border-color: #28a745;
            background: #f0f9f4;
        }
        .card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .card-title {
            font-size: 17px;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }
        .card-desc {
            color: var(--medex-muted);
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 12px;
        }
        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--medex-border);
        }

        /* Overview Cards */
        .overview-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        @media (max-width: 1200px) {
            .overview-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 700px) {
            .overview-grid { grid-template-columns: 1fr; }
        }
        .overview-card {
            background: var(--medex-surface);
            border: 1px solid var(--medex-border);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 10px 24px rgba(15, 75, 143, 0.07);
            transition: all 0.2s ease;
        }
        .overview-card:hover {
            box-shadow: 0 12px 26px rgba(15, 75, 143, 0.11);
            transform: translateY(-1px);
        }
        .overview-card h3 {
            margin: 0 0 20px 0;
            font-size: 18px;
            font-weight: 600;
            color: var(--medex-text);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .overview-card h3 i {
            color: var(--medex-primary);
            font-size: 20px;
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .status-badge.success {
            background: #d4edda;
            color: #155724;
        }
        .status-badge.warning {
            background: #fff3cd;
            color: #856404;
        }
        .status-badge.error {
            background: #f8d7da;
            color: #721c24;
        }

        /* Stats Display */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .stat-item:last-child {
            border-bottom: none;
        }
        .stat-label {
            color: var(--medex-muted);
            font-size: 14px;
        }
        .stat-value {
            font-size: 18px;
            font-weight: 600;
            color: var(--medex-text);
        }

        /* Button System */
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary {
            background: var(--medex-primary);
            color: white;
        }
        .btn-primary:hover {
            background: var(--medex-primary-dark);
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-outline {
            background: white;
            border: 2px solid var(--medex-primary);
            color: var(--medex-primary);
        }
        .btn-outline:hover {
            background: var(--medex-primary);
            color: white;
        }
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Badge System */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge.current {
            background: #d4edda;
            color: #155724;
        }
        .badge.trial {
            background: #fff3cd;
            color: #856404;
        }
        .badge.inactive {
            background: #e0e0e0;
            color: #666;
        }

        /* Alert System */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .alert i {
            font-size: 18px;
            flex-shrink: 0;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Loading Spinner */
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .loading i {
            font-size: 32px;
            color: var(--medex-primary);
            margin-bottom: 15px;
        }

        /* Subscription-Specific Styles - Preserve Original Cool UX */
        #service-list {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 12px !important;
        }
        @media (max-width: 1400px) {
            #service-list {
                grid-template-columns: repeat(3, 1fr) !important;
            }
        }
        @media (max-width: 1000px) {
            #service-list {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
        @media (max-width: 600px) {
            #service-list {
                grid-template-columns: 1fr !important;
            }
        }
        .service-card {
            border: 2px solid var(--medex-border);
            border-radius: 10px;
            padding: 12px;
            transition: all 0.2s ease;
            position: relative;
            background: var(--medex-surface);
            display: flex;
            flex-direction: column;
            min-height: 180px;
        }
        .service-card.active {
            border-color: #9ac0e2;
            background: var(--medex-panel-bg);
        }
        .service-card.selected {
            border-color: #28a745;
            background: #f0f9f4;
        }
        .service-card.removing {
            border-color: #dc3545;
            background: #fff5f5;
        }
        .service-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .service-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .service-title {
            font-size: 17px;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .service-title i {
            color: var(--medex-primary);
        }
        .service-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .service-status.current {
            background: #d4edda;
            color: #155724;
        }
        .service-status.trial {
            background: #fff3cd;
            color: #856404;
        }
        .service-desc {
            color: var(--medex-muted);
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 12px;
        }
        .service-pricing {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
            padding-top: 10px;
            border-top: 1px solid var(--medex-border);
        }
        .service-price {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        .service-trial {
            color: #28a745;
            font-weight: 600;
            font-size: 14px;
        }
        .service-action {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .btn-sm {
            padding: 8px 16px;
            font-size: 14px;
        }
        .provider-selector {
            margin-top: 15px;
            padding: 0;
            background: #f3f8fd;
            border-radius: 8px;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.3s ease, padding 0.3s ease, margin-top 0.3s ease, opacity 0.3s ease;
        }
        .provider-selector:not(.collapsed) {
            max-height: 300px;
            opacity: 1;
            padding: 15px;
        }
        .cart-item {
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .form-check-input {
            margin-right: 8px;
        }
        .form-check-label {
            user-select: none;
        }

        /* Activity List */
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .activity-icon i {
            font-size: 14px;
            color: var(--medex-primary);
        }
        .activity-content {
            flex: 1;
        }
        .activity-text {
            margin: 0 0 4px 0;
            font-size: 14px;
            color: #333;
        }
        .activity-time {
            font-size: 12px;
            color: #999;
        }

        /* Loading State */
        .loading {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .loading i {
            font-size: 32px;
            margin-bottom: 10px;
        }

        /* Toast Notifications */
        .toast {
            position: fixed;
            top: 80px;
            right: 20px;
            background: white;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: none;
            align-items: center;
            gap: 12px;
            z-index: 2000;
            min-width: 300px;
        }
        .toast.show {
            display: flex;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .toast.success { border-left: 4px solid #28a745; }
        .toast.error { border-left: 4px solid #dc3545; }
        .toast.info { border-left: 4px solid #17a2b8; }
        .toast-icon {
            font-size: 24px;
        }
        .toast-icon.success { color: #28a745; }
        .toast-icon.error { color: #dc3545; }
        .toast-icon.info { color: #17a2b8; }
        .toast-message {
            flex: 1;
            font-size: 14px;
            color: #333;
        }
        .toast-close {
            cursor: pointer;
            color: #999;
            font-size: 18px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-header-content {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
                padding: 10px 16px 14px;
            }
            .dashboard-actions {
                width: 100%;
                justify-content: space-between;
                flex-wrap: wrap;
            }
            .tab-navigation-inner {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .overview-grid {
                grid-template-columns: 1fr;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Compact Header with Tabs -->
    <div class="dashboard-header">
        <div class="dashboard-header-content">
            <div class="tab-navigation-inner">
                <div class="dashboard-brand"><?php echo xlt('MedEx Admin Dashboard'); ?></div>
                <a href="#" data-tab="overview" onclick="return switchToTab('overview');" class="tab-link <?php echo $currentTab === 'overview' ? 'active' : ''; ?>">
                    <i class="fa fa-home"></i>
                    <?php echo xlt('Overview'); ?>
                </a>
                <?php if ($isActive): ?>
                    <div class="menu-anchor">
                        <a href="#" id="services-tab-link" data-tab="subscriptions" onclick="return switchToTab('subscriptions');" class="tab-link <?php echo $currentTab === 'subscriptions' ? 'active' : ''; ?>">
                            <i class="fa fa-shopping-cart"></i>
                            <?php echo xlt('Services'); ?>
                            <span class="menu-caret"><i class="fa fa-caret-down"></i></span>
                        </a>
                        <div id="services-dropdown" class="menu-dropdown" aria-label="Services Menu"></div>
                    </div>
                    <a href="#" data-tab="settings" onclick="return switchToTab('settings');" class="tab-link <?php echo $currentTab === 'settings' ? 'active' : ''; ?>">
                        <i class="fa fa-cog"></i>
                        <?php echo xlt('Settings'); ?>
                    </a>
                    <a href="#" data-tab="backups" onclick="return switchToTab('backups');" class="tab-link <?php echo $currentTab === 'backups' ? 'active' : ''; ?>">
                        <i class="fa fa-history"></i>
                        <?php echo xlt('Backups'); ?>
                    </a>
                <?php else: ?>
                    <span class="tab-link disabled">
                        <i class="fa fa-shopping-cart"></i>
                        <?php echo xlt('Services'); ?>
                    </span>
                    <span class="tab-link disabled">
                        <i class="fa fa-cog"></i>
                        <?php echo xlt('Settings'); ?>
                    </span>
                    <span class="tab-link disabled">
                        <i class="fa fa-history"></i>
                        <?php echo xlt('Backups'); ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="dashboard-actions">
                <?php if ($isActive && $hasActiveSubscriptions): ?>
                    <button type="button" class="btn btn-sync-header" id="sync-button" onclick="triggerSync(this)" title="<?php echo xla('Sync data with MedEx server'); ?>">
                        <i class="fa fa-sync-alt"></i>
                        <?php echo xlt('Sync Now'); ?>
                    </button>
                <?php endif; ?>
                <a
                    href="<?php echo attr($helpCenterUrl); ?>"
                    class="help-center-link"
                    onclick="return openProductionReadiness(this.href);"
                >
                    <i class="fa fa-life-ring"></i>
                    <?php echo xlt('Help Center'); ?>
                </a>
                <button type="button" class="embedded-close" onclick="closeConfigModal()">
                    <i class="fa fa-times"></i> <?php echo xlt('Close'); ?>
                </button>
            </div>
        </div>
    </div>
    <div class="context-bar" id="medex-context-bar"><?php echo xlt('Dashboard > Overview'); ?></div>

    <!-- Main Container -->
    <div class="dashboard-container">
        <?php
        // Show lock/force_disable banner if server has remotely disabled this account
        $lockReason = ($api && method_exists($api, 'getLockReason')) ? $api->getLockReason() : null;
        if ($lockReason):
        ?>
        <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:6px;padding:16px 20px;margin:20px;display:flex;align-items:center;gap:12px;">
            <i class="fa fa-ban" style="color:#dc3545;font-size:1.4em;"></i>
            <div>
                <strong style="color:#856404;"><?php echo xlt('MedEx Account Disabled'); ?></strong><br>
                <span style="color:#856404;font-size:13px;"><?php echo text($lockReason); ?></span>
                <span style="color:#856404;font-size:13px;"> &mdash; <?php echo xlt('Contact MedEx support to resolve.'); ?></span>
            </div>
        </div>
        <?php endif; ?>
        <!-- Overview Tab -->
        <div class="tab-content <?php echo $currentTab === 'overview' ? 'active' : ''; ?>" id="tab-overview">
            <?php if (!$isConfigured): ?>
                <div class="overview-card">
                    <h3><i class="fa fa-exclamation-circle"></i> <?php echo xlt('Setup Required'); ?></h3>
                    <p style="color: #666; margin-bottom: 20px;">
                        <?php echo xlt('MedEx is not configured yet. Please complete the registration process to get started.'); ?>
                    </p>
                    <a href="splash.php" class="btn btn-primary">
                        <i class="fa fa-rocket"></i> <?php echo xlt('Get Started'); ?>
                    </a>
                </div>
            <?php else: ?>
                <div id="overview-content">
                    <div class="loading">
                        <i class="fa fa-spinner fa-spin"></i>
                        <p><?php echo xlt('Loading dashboard...'); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Subscriptions Tab -->
        <div class="tab-content <?php echo $currentTab === 'subscriptions' ? 'active' : ''; ?>" id="tab-subscriptions">
            <div class="loading">
                <i class="fa fa-spinner fa-spin"></i>
                <p><?php echo xlt('Loading subscriptions...'); ?></p>
            </div>
        </div>

        <!-- Settings Tab -->
        <div class="tab-content <?php echo $currentTab === 'settings' ? 'active' : ''; ?>" id="tab-settings">
            <div class="loading">
                <i class="fa fa-spinner fa-spin"></i>
                <p><?php echo xlt('Loading settings...'); ?></p>
            </div>
        </div>

        <!-- Backups Tab -->
        <div class="tab-content <?php echo $currentTab === 'backups' ? 'active' : ''; ?>" id="tab-backups">
            <div class="loading">
                <i class="fa fa-spinner fa-spin"></i>
                <p><?php echo xlt('Loading backups...'); ?></p>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <i class="toast-icon fa"></i>
        <div class="toast-message"></div>
        <span class="toast-close" onclick="hideToast()">&times;</span>
    </div>

    <script>
        const csrfToken = <?php echo json_encode($csrfToken); ?>;
        const isConfigured = <?php echo json_encode($isConfigured); ?>;
        const hasActiveSubscriptions = <?php echo json_encode($hasActiveSubscriptions); ?>;
        const currentTab = <?php echo json_encode($currentTab); ?>;
        const siteId = <?php echo json_encode($_SESSION['site_id'] ?? 'default'); ?>;
        const tabContextMap = {
            overview: 'Dashboard > Overview',
            subscriptions: 'Dashboard > Services',
            settings: 'Dashboard > Settings',
            backups: 'Dashboard > Backups'
        };
        let _servicesMenuBuilt = false;
        if (window.top && window.top !== window.self) {
            document.body.classList.add('in-config-modal');
        }

        window.medexSetContext = function medexSetContext(path) {
            const bar = document.getElementById('medex-context-bar');
            if (!bar) return;
            if (Array.isArray(path)) {
                bar.textContent = path.join(' > ');
            } else {
                bar.textContent = String(path || tabContextMap.overview);
            }
        };

        function closeServicesDropdown() {
            const dd = document.getElementById('services-dropdown');
            if (dd) dd.classList.remove('open');
        }

        function openServiceFromMenu(serviceId, serviceName) {
            closeServicesDropdown();
            switchToTab('subscriptions');
            window.medexSetContext(['Dashboard', 'Services', serviceName || serviceId, 'Edit']);
            let attempts = 0;
            const openWhenReady = function() {
                if (typeof window.medexOpenServiceView === 'function') {
                    window.medexOpenServiceView(serviceId, serviceName || serviceId);
                    return true;
                }
                attempts += 1;
                return attempts > 60;
            };
            if (openWhenReady()) return false;
            const timer = setInterval(function() {
                if (openWhenReady()) clearInterval(timer);
            }, 100);
            return false;
        }

        function buildServicesDropdown(force) {
            if (_servicesMenuBuilt && !force) return;
            const dd = document.getElementById('services-dropdown');
            if (!dd) return;
            const defs = window.serviceDefinitions || {};
            const keys = Object.keys(defs);
            if (keys.length === 0) {
                dd.innerHTML = '<button class="menu-item" type="button" disabled><i class="fa fa-spinner fa-spin"></i> Loading services...</button>';
                return;
            }
            const items = keys.map(function(id) {
                const svc = defs[id] || {};
                return {
                    id: id,
                    name: svc.name || id,
                    icon: svc.icon || 'fa fa-cube'
                };
            }).sort(function(a, b) {
                return a.name.localeCompare(b.name);
            });
            dd.innerHTML = items.map(function(item) {
                return '<button class="menu-item" type="button" data-service-id="' + item.id + '" data-service-name="' + item.name.replace(/"/g, '&quot;') + '">' +
                    '<i class="' + item.icon + '"></i>' + item.name + '</button>';
            }).join('');
            dd.querySelectorAll('.menu-item[data-service-id]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    openServiceFromMenu(btn.getAttribute('data-service-id'), btn.getAttribute('data-service-name'));
                });
            });
            _servicesMenuBuilt = true;
        }

        function closeConfigModal() {
            try {
                window.top.postMessage({ type: 'medex-close-config-modal' }, '*');
            } catch (e) {}
            try {
                const modal = window.top && window.top.document
                    ? window.top.document.getElementById('medex-module-config-modal')
                    : null;
                if (modal && modal.parentNode) {
                    modal.parentNode.removeChild(modal);
                }
            } catch (e2) {}
        }

        /**
         * OpenEMR session-safe fetch wrapper.
         * Always calls top.restoreSession() before the request so CSRF tokens
         * and the PHP session stay valid.  Drop-in replacement for fetch().
         */
        function medexFetch(url, options) {
            if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
                top.restoreSession();
            }

            // After restoreSession, the cookie-based session is refreshed,
            // but our local 'csrfToken' JS variable might be stale if the
            // server-side secret changed (rare) or if we need to pull a fresh one
            // from the parent's state. OpenEMR's CSRF is usually stable per session.

            return fetch(url, options);
        }

        // Show toast notification
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            const icon = toast.querySelector('.toast-icon');
            const messageEl = toast.querySelector('.toast-message');

            toast.className = 'toast show ' + type;
            icon.className = 'toast-icon fa ' + type;

            if (type === 'success') icon.classList.add('fa-check-circle');
            else if (type === 'error') icon.classList.add('fa-exclamation-circle');
            else icon.classList.add('fa-info-circle');

            messageEl.textContent = message;

            setTimeout(() => hideToast(), 5000);
        }

        function hideToast() {
            document.getElementById('toast').classList.remove('show');
        }

        function openProductionReadiness(url) {
            const msg = 'Open MedEx Help Center?';
            if (!window.confirm(msg)) {
                return false;
            }
            if (window.top && typeof window.top.restoreSession === 'function') {
                window.top.restoreSession();
            }
            if (window.top && window.top !== window) {
                window.top.location.href = url;
                return false;
            }
            window.location.href = url;
            return false;
        }

        // Toggle MedEx Enable/Disable
        function toggleMedExEnable(checkbox) {
            if (!hasActiveSubscriptions && checkbox.checked) {
                showToast('Activate at least one service before enabling MedEx', 'error');
                checkbox.checked = false;
                return;
            }
            const formData = new FormData();
            formData.append('csrf_token_form', csrfToken);
            formData.append('medex_enable', checkbox.checked ? '1' : '0');

            medexFetch('save_preferences.php?site=' + encodeURIComponent(siteId), {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(checkbox.checked ? 'MedEx enabled' : 'MedEx disabled', 'success');
                    // Reload page to update connection status
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error: ' + (data.error || 'Unknown error'), 'error');
                    // Revert checkbox
                    checkbox.checked = !checkbox.checked;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating MedEx status', 'error');
                // Revert checkbox
                checkbox.checked = !checkbox.checked;
            });
        }

        // Trigger sync
        function triggerSync(buttonOverride) {
            if (!hasActiveSubscriptions) {
                showToast('No active subscriptions to sync', 'error');
                return;
            }
            const button = buttonOverride || document.getElementById('sync-button') || document.getElementById('sync-button-overview');
            if (!button) {
                showToast('Sync button unavailable', 'error');
                return;
            }
            const icon = button.querySelector('i');

            button.disabled = true;
            button.classList.add('syncing');
            if (icon) {
                icon.classList.add('fa-spin');
            }

            console.log('[MedEx] Triggering sync with CSRF token:', csrfToken ? csrfToken.substring(0, 20) + '...' : 'MISSING');

            medexFetch('api/sync_now.php?site=' + encodeURIComponent(siteId), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ csrf_token: csrfToken })
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Sync HTTP error:', response.status, text);
                        throw new Error(`HTTP ${response.status}: ${text.substring(0, 100)}`);
                    });
                }
                return response.text().then(text => {
                    console.log('Sync response:', text);
                    if (!text) {
                        throw new Error('Empty response from server');
                    }
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    showToast('Practice data synced successfully', 'success');
                    // Reload overview if on that tab
                    if (currentTab === 'overview' && isConfigured) {
                        loadOverview();
                    }
                } else {
                    showToast(data.error || 'Sync failed', 'error');
                }
            })
            .catch(error => {
                console.error('Sync error:', error);
                showToast('Sync error: ' + error.message, 'error');
            })
            .finally(() => {
                button.disabled = false;
                button.classList.remove('syncing');
                if (icon) {
                    icon.classList.remove('fa-spin');
                }
            });
        }

        // Switch between tabs without page reload
        function switchToTab(tab) {
            closeServicesDropdown();
            // Update active state on tab links
            document.querySelectorAll('.tab-link[data-tab]').forEach(function(el) {
                el.classList.toggle('active', el.dataset.tab === tab);
            });
            // Show/hide tab content divs
            document.querySelectorAll('.tab-content').forEach(function(el) {
                el.classList.remove('active');
            });
            var contentDiv = document.getElementById('tab-' + tab);
            if (contentDiv) {
                contentDiv.classList.add('active');
            }
            // Lazy-load content if not yet loaded
            loadTabContent(tab);
            window.medexSetContext(tabContextMap[tab] || tabContextMap.overview);
            // Update URL for bookmarking without a full page reload
            if (window.history && window.history.replaceState) {
                var params = new URLSearchParams(window.location.search);
                params.set('tab', tab);
                if (siteId) params.set('site', siteId);
                window.history.replaceState(null, '', '?' + params.toString());
            }
            return false;
        }

        // Load tab content
        function loadTabContent(tab) {
            const contentDiv = document.getElementById('tab-' + tab);
            if (!contentDiv || contentDiv.dataset.loaded === 'true') return;

            let endpoint = '';
            switch(tab) {
                case 'overview':
                    endpoint = 'api/get_overview.php?site=' + encodeURIComponent(siteId);
                    break;
                case 'subscriptions':
                    endpoint = 'api/get_subscriptions.php?site=' + encodeURIComponent(siteId);
                    break;
                case 'settings':
                    endpoint = 'api/get_settings.php?site=' + encodeURIComponent(siteId);
                    break;
                case 'backups':
                    endpoint = 'api/get_backups.php?site=' + encodeURIComponent(siteId);
                    break;
            }

            if (endpoint) {
                medexFetch(endpoint, {
                    credentials: 'same-origin'
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                        }
                        return response.text();
                    })
                    .then(html => {
                        if (!html || html.trim() === '') {
                            throw new Error('Empty response from server');
                        }

                        // Parse HTML and extract scripts before setting innerHTML
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = html;
                        const scriptTags = tempDiv.querySelectorAll('script');
                        const scripts = [];

                        // Extract script content
                        scriptTags.forEach(script => {
                            scripts.push({
                                src: script.src,
                                content: script.textContent || script.innerHTML
                            });
                            script.remove(); // Remove from HTML
                        });

                        // Set content without scripts
                        contentDiv.innerHTML = tempDiv.innerHTML;
                        contentDiv.dataset.loaded = 'true';

                        // Execute scripts
                        scripts.forEach((script, index) => {
                            if (script.src) {
                                // External script
                                const newScript = document.createElement('script');
                                newScript.src = script.src;
                                document.head.appendChild(newScript);
                            } else if (script.content && script.content.trim()) {
                                // Inline script - check for syntax errors first
                                console.log(`[Script ${index}] Length: ${script.content.length}, First 100 chars:`, script.content.substring(0, 100));
                                console.log(`[Script ${index}] Last 100 chars:`, script.content.substring(script.content.length - 100));

                                try {
                                    // Try to validate syntax by creating a function
                                    new Function(script.content);
                                    console.log(`[Script ${index}] Syntax is valid`);

                                    // Now execute it
                                    const newScript = document.createElement('script');
                                    newScript.text = script.content;
                                    document.body.appendChild(newScript);
                                    console.log(`[Script ${index}] Executed successfully`);
                                } catch (e) {
                                    console.error(`[Script ${index}] Syntax or execution error:`, e);
                                    console.log(`[Script ${index}] Full content:`, script.content);
                                }
                            }
                        });
                        if (tab === 'subscriptions') {
                            buildServicesDropdown(true);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading ' + tab + ':', error);
                        contentDiv.innerHTML = '<div class="overview-card"><p style="color: #dc3545;">Error loading content: ' + error.message + '</p></div>';
                    });
            }
        }

        // Load overview specifically
        function loadOverview() {
            const overviewDiv = document.getElementById('overview-content');
            if (!overviewDiv) return;

            overviewDiv.innerHTML = '<div class="loading"><i class="fa fa-spinner fa-spin"></i><p><?php echo xla('Loading dashboard...'); ?></p></div>';

            medexFetch('api/get_overview.php', {
                credentials: 'same-origin'
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                    }
                    return response.text();
                })
                .then(html => {
                    if (!html || html.trim() === '') {
                        throw new Error('Empty response from server');
                    }
                    overviewDiv.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading overview:', error);
                    overviewDiv.innerHTML = '<div class="overview-card"><p style="color: #dc3545;">Error loading overview: ' + error.message + '</p></div>';
                });
        }

        // Subscription Management Functions
        let subscriptionCart = [];
        let braintreeInstance = null;

        function addService(serviceId) {
            const card = document.querySelector(`.service-card[data-service="${serviceId}"]`);
            if (!card) return;

            card.classList.add('selected');
            card.classList.remove('active');

            // Show provider selector
            const providerSelector = document.getElementById(`providers-${serviceId}`);
            if (providerSelector) {
                providerSelector.classList.remove('collapsed');
            }

            // Add to cart
            if (!subscriptionCart.includes(serviceId)) {
                subscriptionCart.push(serviceId);
            }

            updateCartDisplay();
            document.getElementById('review-changes-btn').style.display = 'block';
        }

        function removeService(serviceId) {
            const card = document.querySelector(`.service-card[data-service="${serviceId}"]`);
            if (!card) return;

            card.classList.add('removing');
            card.classList.remove('active');

            // Add to removal list
            if (!subscriptionCart.includes('remove_' + serviceId)) {
                subscriptionCart.push('remove_' + serviceId);
            }

            updateCartDisplay();
            document.getElementById('review-changes-btn').style.display = 'block';
        }

        function updateCartDisplay() {
            if (!window.serviceDefinitions) return;

            const cartItemsContainer = document.getElementById('cart-items');
            const cartTotalElement = document.getElementById('cart-total');

            if (!cartItemsContainer || !cartTotalElement) return;

            // Calculate current state
            let cartHTML = '';
            let total = 0;
            let hasChanges = false;

            // Build cart from subscription cart
            subscriptionCart.forEach(item => {
                if (item.startsWith('remove_')) {
                    hasChanges = true;
                } else if (window.serviceDefinitions[item]) {
                    const service = window.serviceDefinitions[item];
                    total += service.price;
                    hasChanges = true;

                    cartHTML += `
                        <div class="cart-item" data-service="${item}">
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #e0e0e0;">
                                <div>
                                    <strong>${service.name}</strong>
                                    <span class="badge" style="margin-left: 8px; background: #ffc107;">New</span>
                                </div>
                                <div style="text-align: right;">
                                    <strong>$${service.price.toFixed(2)}</strong>
                                </div>
                            </div>
                        </div>
                    `;
                }
            });

            // Add existing subscriptions not being removed
            if (window.currentSubscriptions && Array.isArray(window.currentSubscriptions)) {
                window.currentSubscriptions.forEach(sub => {
                    if (!subscriptionCart.includes('remove_' + sub.service_id) && window.serviceDefinitions[sub.service_id]) {
                        const service = window.serviceDefinitions[sub.service_id];
                        total += service.price;

                        cartHTML += `
                            <div class="cart-item" data-service="${sub.service_id}">
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #e0e0e0;">
                                    <div>
                                        <strong>${service.name}</strong>
                                        ${sub.status === 'trial' ? '<span class="badge trial" style="margin-left: 8px;">Trial</span>' : ''}
                                    </div>
                                    <div style="text-align: right;">
                                        <strong>$${service.price.toFixed(2)}</strong>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                });
            }

            if (cartHTML === '') {
                cartHTML = `
                    <div class="alert alert-info">
                        <i class="fa fa-shopping-cart"></i>
                        <div>No services selected yet.</div>
                    </div>
                `;
            }

            cartItemsContainer.innerHTML = cartHTML;
            cartTotalElement.textContent = '$' + total.toFixed(2);

            // Show review button if there are changes
            if (hasChanges) {
                document.getElementById('review-changes-btn').style.display = 'block';
            }
        }

        function showPaymentForm() {
            document.getElementById('payment-section').style.display = 'block';
            document.getElementById('review-changes-btn').style.display = 'none';

            if (window.braintreeToken && !braintreeInstance) {
                initBraintree();
            }
        }

        function initBraintree() {
            if (!window.braintreeToken) {
                console.error('[Subscriptions] Braintree not loaded or no token available');
                return;
            }
            // New flow uses Hosted Fields + Apple Pay helpers defined in get_subscriptions.php.
            if (typeof window._initMedexPaymentComponents === 'function') {
                window._medexBraintreeToken = window.braintreeToken;
                window._initMedexPaymentComponents();
                return;
            }
            console.error('[Subscriptions] Payment helpers not available yet');
        }

        function processPayment() {
            if (typeof window.processPayment === 'function' && window.processPayment !== processPayment) {
                window.processPayment();
                return;
            }
            // Legacy fallback path only.
            const instance = braintreeInstance;
            if (!instance || typeof instance.requestPaymentMethod !== 'function') {
                showToast('Payment system not initialized. Please reload and try again.', 'error');
                console.error('[MedEx] processPayment: payment helpers unavailable');
                return;
            }

            const submitBtn = document.getElementById('payment-submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';

            instance.requestPaymentMethod(function (err, payload) {
                if (err) {
                    console.error('[Subscriptions] Payment method error:', err);
                    showToast('Payment failed: ' + err.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa fa-check"></i> Complete Subscription Changes';
                    return;
                }

                // Use window.pendingChanges (populated by get_subscriptions.php) when available,
                // otherwise fall back to legacy subscriptionCart format.
                const changes = window.pendingChanges || {add: subscriptionCart, remove: []};
                const siteParam = '?site=' + encodeURIComponent(siteId);
                // Send payment to server
                medexFetch('process_subscription.php' + siteParam, {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        add: changes.add,
                        remove: changes.remove,
                        payment_nonce: payload.nonce,
                        use_existing_payment: false,
                        dev_bypass: false,
                        providers: changes.providers || {}
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Subscription updated successfully!', 'success');
                        // Reload subscriptions tab
                        document.getElementById('tab-subscriptions').dataset.loaded = 'false';
                        loadTabContent('subscriptions');
                        // Reload overview
                        if (window.loadOverview) {
                            loadOverview();
                        }
                    } else {
                        showToast(data.error || 'Subscription update failed', 'error');
                    }
                })
                .catch(error => {
                    console.error('[Subscriptions] Server error:', error);
                    showToast('Server error: ' + error.message, 'error');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa fa-check"></i> Complete Subscription Changes';
                });
            });
        }

        // Agreement checkbox handler
        document.addEventListener('change', function(e) {
            if (e.target.id === 'agree-baa' || e.target.id === 'agree-terms') {
                const baaCheckbox = document.getElementById('agree-baa');
                const termsCheckbox = document.getElementById('agree-terms');
                const submitButton = document.getElementById('payment-submit-btn');
                if (baaCheckbox && termsCheckbox && submitButton) {
                    submitButton.disabled = !(baaCheckbox.checked && termsCheckbox.checked);
                }
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const servicesTab = document.getElementById('services-tab-link');
            const servicesDropdown = document.getElementById('services-dropdown');
            if (servicesTab && servicesDropdown) {
                servicesTab.addEventListener('click', function(e) {
                    if (servicesTab.classList.contains('active') && !e.target.closest('.menu-caret')) {
                        e.preventDefault();
                        buildServicesDropdown(false);
                        servicesDropdown.classList.toggle('open');
                        return;
                    }
                    if (e.target && (e.target.closest('.menu-caret') || e.target.classList.contains('menu-caret'))) {
                        e.preventDefault();
                        buildServicesDropdown(false);
                        servicesDropdown.classList.toggle('open');
                    } else {
                        closeServicesDropdown();
                    }
                });
                servicesTab.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                    buildServicesDropdown(false);
                    servicesDropdown.classList.toggle('open');
                });
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.menu-anchor')) {
                        closeServicesDropdown();
                    }
                });
            }
            // Load current tab content
            if (isConfigured) {
                loadTabContent(currentTab);
            }
            window.medexSetContext(tabContextMap[currentTab] || tabContextMap.overview);
        });
    </script>
</body>
</html>
