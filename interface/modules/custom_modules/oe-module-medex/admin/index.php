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
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

// Check if user is registered
$isConfigured = $api->isConfigured();
$isActive = $isConfigured && $api->isActive();

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

// Get CSRF token
$session = SessionWrapperFactory::getInstance()->getActiveSession();
$csrfToken = CsrfUtils::collectCsrfToken(session: $session);
$siteId = $_SESSION['site_id'] ?? ($_GET['site'] ?? 'default');
$helpCenterUrl = ($GLOBALS['webroot'] ?? '')
    . '/interface/modules/custom_modules/oe-module-medex/admin/help_center.php?site=' . urlencode((string)$siteId);

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('MedEx Admin Dashboard'); ?></title>
    <?php Header::setupHeader(['opener']); ?>
    <style>
        body {
            font-size: 14px;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        /* Compact Header with Tabs */
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .dashboard-header-content {
            max-width: 100%;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
        }
        .tab-navigation-inner {
            display: flex;
            gap: 0;
            align-items: center;
            height: 100%;
        }
        .dashboard-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .sync-status {
            font-size: 13px;
            color: white;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: rgba(255,255,255,0.15);
            border-radius: 6px;
        }
        .sync-button {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sync-button:hover {
            background: rgba(255,255,255,0.3);
        }
        .sync-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .sync-button.syncing {
            background: rgba(255,255,255,0.3);
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
            color: #102d4c;
            background: #f4fbff;
            border: 1px solid #b8def7;
            transition: all 0.2s ease;
        }
        .help-center-link:hover {
            background: #ffffff;
            border-color: #ffffff;
            color: #0b4a82;
            box-shadow: 0 8px 18px rgba(7, 39, 74, 0.22);
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
            background: rgba(255,255,255,0.3);
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
            padding: 0 24px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            border-bottom: 3px solid transparent;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            height: 100%;
            position: relative;
        }
        .tab-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .tab-link.active {
            color: white;
            border-bottom-color: white;
            background: rgba(255,255,255,0.15);
        }
        .tab-link.disabled {
            color: rgba(255,255,255,0.3);
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
            padding: 30px;
            background: #f5f5f5;
            min-height: calc(100vh - 60px);
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
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        .panel h3 {
            margin: 0 0 20px 0;
            font-size: 20px;
            font-weight: 600;
            color: #333;
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
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.2s ease;
            position: relative;
        }
        .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .card.active {
            border-color: #667eea;
            background: #f8f9ff;
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
            color: #666;
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
            border-top: 1px solid #e0e0e0;
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
            background: #f8f9ff;
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.2s ease;
        }
        .overview-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .overview-card h3 {
            margin: 0 0 20px 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .overview-card h3 i {
            color: #667eea;
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
            color: #666;
            font-size: 14px;
        }
        .stat-value {
            font-size: 18px;
            font-weight: 600;
            color: #333;
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
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
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
            border: 2px solid #667eea;
            color: #667eea;
        }
        .btn-outline:hover {
            background: #667eea;
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
            color: #667eea;
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
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px;
            transition: all 0.2s ease;
            position: relative;
            background: white;
            display: flex;
            flex-direction: column;
            min-height: 180px;
        }
        .service-card.active {
            border-color: #667eea;
            background: #f8f9ff;
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
            color: #667eea;
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
            color: #666;
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
            border-top: 1px solid #e0e0e0;
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
            background: #f9f9f9;
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
            color: #667eea;
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
                <a href="#" data-tab="overview" onclick="return switchToTab('overview');" class="tab-link <?php echo $currentTab === 'overview' ? 'active' : ''; ?>">
                    <i class="fa fa-home"></i>
                    <?php echo xlt('Overview'); ?>
                </a>
                <?php if ($isActive): ?>
                    <a href="#" data-tab="subscriptions" onclick="return switchToTab('subscriptions');" class="tab-link <?php echo $currentTab === 'subscriptions' ? 'active' : ''; ?>">
                        <i class="fa fa-shopping-cart"></i>
                        <?php echo xlt('Services'); ?>
                    </a>
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
                <a
                    href="<?php echo attr($helpCenterUrl); ?>"
                    class="help-center-link"
                    onclick="return openProductionReadiness(this.href);"
                >
                    <i class="fa fa-life-ring"></i>
                    <?php echo xlt('Help Center'); ?>
                </a>
                <label class="header-toggle-switch" style="color: white; font-size: 14px; margin: 0;">
                    <input type="checkbox" id="medex_enable_header" value="1" <?php echo ($GLOBALS['medex_enable'] ?? '0') == '1' ? 'checked' : ''; ?> onchange="toggleMedExEnable(this)">
                    <span style="margin-left: 8px;"><?php echo xlt('MedEx Enabled'); ?></span>
                </label>
                <button class="sync-button" id="sync-button" onclick="triggerSync()"
                    <?php echo (!$isActive || !$hasActiveSubscriptions) ? 'disabled' : ''; ?>
                    title="<?php echo !$hasActiveSubscriptions ? xla('No active subscriptions to sync') : xla('Sync data with MedEx server'); ?>">
                    <i class="fa fa-sync-alt"></i>
                    <?php echo xlt('Sync Now'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="dashboard-container">
        <?php
        // Show lock/force_disable banner if server has remotely disabled this account
        $lockReason = $api->getLockReason();
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
        const currentTab = <?php echo json_encode($currentTab); ?>;
        const siteId = <?php echo json_encode($_SESSION['site_id'] ?? 'default'); ?>;

        /**
         * OpenEMR session-safe fetch wrapper.
         * Always calls top.restoreSession() before the request so CSRF tokens
         * and the PHP session stay valid.  Drop-in replacement for fetch().
         */
        function medexFetch(url, options) {
            if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
                top.restoreSession();
            }
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
        function triggerSync() {
            const button = document.getElementById('sync-button');
            const icon = button.querySelector('i');

            button.disabled = true;
            button.classList.add('syncing');
            icon.classList.add('fa-spin');

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
                icon.classList.remove('fa-spin');
            });
        }

        // Switch between tabs without page reload
        function switchToTab(tab) {
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
            if (!window.braintree || !window.braintreeToken) {
                console.error('[Subscriptions] Braintree not loaded or no token available');
                return;
            }

            braintree.dropin.create({
                authorization: window.braintreeToken,
                container: '#braintree-dropin-container',
                locale: 'en_US'
            }, function (err, instance) {
                if (err) {
                    console.error('[Subscriptions] Braintree error:', err);
                    showToast('Payment system error', 'error');
                    return;
                }
                braintreeInstance = instance;
            });
        }

        function processPayment() {
            // Prefer the drop-in instance from get_subscriptions.php (window._medexDropinInstance),
            // fall back to the legacy local braintreeInstance.
            const instance = window._medexDropinInstance || braintreeInstance;
            if (!instance) {
                showToast('Payment system not initialized. Please reload and try again.', 'error');
                console.error('[MedEx] processPayment: no Braintree instance. _medexDropinInstance:', window._medexDropinInstance, 'braintreeInstance:', braintreeInstance);
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
            // Load current tab content
            if (isConfigured) {
                loadTabContent(currentTab);
            }
        });
    </script>
    <!-- Braintree Drop-in SDK -->
    <script src="https://js.braintreegateway.com/web/dropin/1.43.0/js/dropin.min.js"></script>
</body>
</html>
