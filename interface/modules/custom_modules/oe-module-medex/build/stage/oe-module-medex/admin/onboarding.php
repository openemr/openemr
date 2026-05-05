<?php
/**
 * MedEx Module - Onboarding Wizard
 *
 * Multi-step onboarding process for new practices:
 * Step 1: Account Registration
 * Step 2: Service Configuration (Providers & Add-ons)
 * Step 3: Payment & Activation (Handoff to SaaS)
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Modules\MedEx\ComponentLoader;
use OpenEMR\Modules\MedEx\MedExConfig;

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo "Access denied";
    exit;
}

// Load MedEx API
require_once(__DIR__ . '/../src/MedExAPI.php');
require_once(__DIR__ . '/../src/ComponentLoader.php');
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

function medexResolveOpenEmrBaseUrlOnboarding(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '') {
        return '';
    }

    $basePath = '';
    $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $interfacePos = strpos($scriptName, '/interface/');
    if ($interfacePos !== false) {
        $basePath = rtrim(substr($scriptName, 0, $interfacePos), '/');
    }

    if ($basePath === '') {
        $webroot = trim((string)($GLOBALS['webroot'] ?? ''), '/');
        if ($webroot !== '') {
            $basePath = '/' . $webroot;
        }
    }

    if ($basePath === '') {
        $siteAddr = trim((string)($GLOBALS['site_addr_oath'] ?? ''));
        if ($siteAddr !== '') {
            $siteParts = parse_url($siteAddr);
            $sitePath = trim((string)($siteParts['path'] ?? ''), '/');
            if ($sitePath !== '') {
                $basePath = '/' . $sitePath;
            }
        }
    }

    return $scheme . '://' . $host . $basePath;
}

$step = $_GET['step'] ?? '1';
$isConfigured = $api->isConfigured();
$session = null;
if (class_exists(SessionWrapperFactory::class)) {
    try {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
    } catch (\Throwable $e) {
        $session = null;
    }
}
if ($session) {
    if (empty($session->get('csrf_private_key', null))) {
        CsrfUtils::setupCsrfKey($session);
    }
    $csrfToken = (string) CsrfUtils::collectCsrfToken(session: $session);
} else {
    if (empty($_SESSION['csrf_private_key'] ?? null)) {
        CsrfUtils::setupCsrfKey();
    }
    $csrfToken = (string) CsrfUtils::collectCsrfToken('default');
}
$termsVersion = MedExConfig::TERMS_VERSION;
$baaVersion = MedExConfig::BAA_VERSION;
$onboardingVerificationWindowSeconds = 14400;
$termsUrl = MedExConfig::termsUrl();
$baaUrl = MedExConfig::baaUrl();
$privacyUrl = MedExConfig::privacyUrl();
$siteId = (string)($_GET['site'] ?? 'default');
$forceOnboarding = !empty($_GET['force_onboarding']);
$agreementSignUrlBase = 'agreement_sign.php?site=' . urlencode($siteId);
$sessionCartItems = (isset($_SESSION['medex_cart_items']) && is_array($_SESSION['medex_cart_items'])) ? $_SESSION['medex_cart_items'] : [];
$sessionCartTotal = isset($_SESSION['medex_cart_total']) ? (float)$_SESSION['medex_cart_total'] : null;
$loginData = [];
$braintreeToken = null;
$defaultOpenEmrUrl = medexResolveOpenEmrBaseUrlOnboarding();

// Fetch pricing from API for step 2.
// For configured accounts, force a fresh login first so customer_group_id is current
// before pricing is resolved on the MedEx side.
if ($isConfigured) {
    try {
        $loginData = $api->login(true);
        $braintreeToken = $loginData['braintree_token'] ?? null;
    } catch (\Throwable $e) {
        error_log('[ONBOARDING DEBUG] Forced login before pricing failed: ' . $e->getMessage());
    }
}
$pricing = $api->getPricing(true);
error_log('[ONBOARDING DEBUG] Pricing data: ' . print_r($pricing, true));

$rawPricingServices = is_array($pricing['services'] ?? null) ? $pricing['services'] : [];
$helpBaseUrl = 'help_center.php?site=' . urlencode((string)$siteId);
$onboardingServices = ComponentLoader::buildServiceCatalog($rawPricingServices, $helpBaseUrl);
$serviceLabels = [];
foreach ($onboardingServices as $serviceKey => $serviceMeta) {
    $serviceLabels[$serviceKey] = (string)($serviceMeta['title'] ?? $serviceKey);
}
$hasAvailableServices = !empty($onboardingServices);
$providerCandidates = QueryUtils::fetchRecords("
    SELECT id, fname, lname, username
    FROM users
    WHERE active = 1
      AND calendar = 1
    ORDER BY username, id DESC
");
$providerRows = [];
$providerSeen = [];
foreach ($providerCandidates as $candidate) {
    $username = strtolower(trim((string)($candidate['username'] ?? '')));
    $fname = trim((string)($candidate['fname'] ?? ''));
    $lname = trim((string)($candidate['lname'] ?? ''));
    $displayName = trim($lname . ', ' . $fname, ' ,');
    $normalizedName = strtolower(trim((string)(preg_replace('/\s+/', ' ', $displayName) ?? $displayName)));
    if (
        $username === ''
        || in_array($username, ['admin', 'oe-system', 'phimail-service', 'portal-user'], true)
        || $normalizedName === ''
        || in_array($normalizedName, ['admin', 'administrator', 'system operation user', 'patient portal user'], true)
    ) {
        continue;
    }
    if (isset($providerSeen[$username])) {
        continue;
    }
    $providerSeen[$username] = true;
    $providerRows[] = [
        'id' => $candidate['id'],
        'fname' => $fname,
        'lname' => $lname,
    ];
}
usort($providerRows, static function (array $a, array $b): int {
    $cmp = strcasecmp((string)($a['lname'] ?? ''), (string)($b['lname'] ?? ''));
    if ($cmp !== 0) {
        return $cmp;
    }
    $cmp = strcasecmp((string)($a['fname'] ?? ''), (string)($b['fname'] ?? ''));
    if ($cmp !== 0) {
        return $cmp;
    }
    return ((int)($a['id'] ?? 0)) <=> ((int)($b['id'] ?? 0));
});
$facilityRows = QueryUtils::fetchRecords("SELECT id, name FROM facility WHERE service_location = 1 ORDER BY name");
if (empty($facilityRows)) {
    $facilityRows = QueryUtils::fetchRecords("SELECT id, name FROM facility ORDER BY name");
}

// If already configured and active, redirect to settings
if (!$forceOnboarding && $isConfigured && $api->isActive()) {
    $services = $api->getEnabledServices();
    if (!empty($services)) {
        header('Location: index.php?site=' . urlencode($siteId));
        exit;
    }
}

// Stage 2 & 3 require registration first
if ($step > 1 && !$isConfigured) {
    header('Location: onboarding.php?step=1&site=' . urlencode($siteId));
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt("MedEx Onboarding"); ?></title>
    <?php Header::setupHeader(['jquery-min-3-7-1', 'fontawesome']); ?>
    <?php if ($step == 3): ?>
    <script src="https://js.braintreegateway.com/web/3.97.2/js/client.min.js"></script>
    <script src="https://js.braintreegateway.com/web/3.97.2/js/hosted-fields.min.js"></script>
    <?php endif; ?>
    <style>
        body { background: linear-gradient(180deg, #eef4fb 0%, #f8fafc 100%); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .wizard-container { max-width: 1100px; margin: 50px auto; background: #ffffff; padding: 32px; border-radius: 16px; border: 1px solid #dbe7f5; box-shadow: 0 18px 44px rgba(15, 23, 42, 0.08); }
        .wizard-header { text-align: center; margin-bottom: 40px; position: relative; }
        .wizard-steps { display: flex; justify-content: space-between; margin-bottom: 40px; position: relative; }
        .wizard-steps::before { content: ''; position: absolute; top: 15px; left: 0; right: 0; height: 2px; background: #e0e0e0; z-index: 1; }
        .wizard-progress-fill { position: absolute; top: 15px; left: 0; height: 2px; width: 0%; background: #0f4b8f; z-index: 1; transition: width 0.25s ease; }
        .step { width: 30px; height: 30px; border-radius: 50%; background: #fff; border: 2px solid #e0e0e0; display: flex; align-items: center; justify-content: center; font-weight: bold; z-index: 2; position: relative; color: #999; }
        .step.active { border-color: #0f4b8f; color: #0f4b8f; }
        .step.completed { background: #0f4b8f; border-color: #0f4b8f; color: #white; }
        .step-label { position: absolute; top: 35px; font-size: 12px; width: 100px; text-align: center; left: 50%; transform: translateX(-50%); color: #999; }
        .step.active .step-label { color: #0f4b8f; font-weight: bold; }

        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #333; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 15px; color: #0f172a; background: #fff; }
        .form-control:focus { outline: none; border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(96,165,250,.18); }
        .form-control.invalid { border-color: #dc2626; }
        .field-error { color: #dc2626; font-size: 12px; margin-top: 6px; display: none; }
        .form-control::placeholder { color: #9ca3af; opacity: 1; }
        select.form-control {
            min-height: 46px;
            line-height: 1.35;
            padding-top: 10px;
            padding-bottom: 10px;
            -webkit-text-fill-color: #0f172a;
            appearance: auto;
        }
        .password-wrap { position: relative; }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            cursor: pointer;
            font-size: 16px;
        }
        .btn { padding: 12px 30px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s; border: none; font-size: 16px; }
        .btn-primary { background: #0f4b8f; color: white; }
        .btn-primary:hover { background: #0a3460; }
        .btn[disabled] { opacity: .55; cursor: not-allowed; box-shadow: none; }

        .service-card { border: 2px solid #e0e0e0; padding: 20px; border-radius: 10px; margin-bottom: 15px; display: flex; align-items: flex-start; gap: 15px; transition: all 0.2s ease; background: #fff; }
        .service-card:hover { border-color: #667eea; background: #f8f9ff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transform: translateY(-1px); }
        .service-info { flex: 1; }
        .service-title-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 6px; }
        .service-title { font-size: 17px; font-weight: 600; color: #333; margin: 0; }
        .service-help-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            border-radius: 999px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #0f4b8f;
            text-decoration: none;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.55);
        }
        .service-help-link:hover { background: #dbeafe; color: #0a3460; text-decoration: none; }
        .service-help-link:focus-visible { outline: 2px solid #2563eb; outline-offset: 2px; }
        .service-desc { font-size: 13px; color: #666; line-height: 1.5; }
        .service-price { font-size: 14px; color: #0f4b8f; font-weight: 600; margin-top: 8px; }
        .service-config-panel {
            display: none;
            margin-top: 16px;
            padding: 16px 18px;
            border: 1px solid #dbeafe;
            border-radius: 12px;
            background: linear-gradient(180deg, #f8fbff 0%, #eff6ff 100%);
        }
        .service-config-panel.show { display: block; }
        .service-config-title { margin: 0 0 6px; font-size: 14px; font-weight: 800; color: #0f4b8f; }
        .service-config-copy { margin: 0 0 12px; font-size: 13px; line-height: 1.5; color: #475569; }
        .service-config-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .service-config-group label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 700; color: #1e293b; }
        .service-config-note { margin-top: 10px; font-size: 12px; color: #64748b; }
        @media (max-width: 760px) {
            .service-config-grid { grid-template-columns: 1fr; }
        }

        .provider-list { max-height: 170px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 6px; margin-top: 10px; background: #fff; }
        .provider-item { display: flex; align-items: center; gap: 10px; padding: 5px 0; border-bottom: 1px solid #f5f5f5; }
        .selection-toolbar { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 8px; }
        .selection-toolbar-copy { font-size: 12px; color: #64748b; font-weight: 600; }
        .selection-toolbar-actions { display: flex; gap: 8px; }
        .selection-link-btn { appearance: none; border: 0; background: transparent; color: #0f4b8f; font-size: 12px; font-weight: 700; padding: 0; cursor: pointer; }
        .selection-link-btn:hover { color: #0a3460; text-decoration: underline; }
        .billing-summary-card { margin-top: 24px; border: 1px solid #d8e5f3; border-radius: 14px; padding: 18px 20px; background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%); }
        .billing-summary-title { margin: 0 0 6px; font-size: 17px; font-weight: 800; color: #132238; }
        .billing-summary-copy { margin: 0 0 14px; font-size: 13px; color: #526277; }
        .billing-summary-list { list-style: none; padding: 0; margin: 0; }
        .billing-summary-item { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 10px 0; border-top: 1px solid #e2e8f0; }
        .billing-summary-item:first-child { border-top: 0; padding-top: 0; }
        .billing-summary-item-name { font-weight: 700; color: #132238; }
        .billing-summary-item-meta { font-size: 12px; color: #64748b; margin-top: 4px; }
        .billing-summary-item-amount { font-weight: 800; color: #0f4b8f; white-space: nowrap; }
        .billing-summary-total { margin-top: 14px; padding-top: 14px; border-top: 2px solid #d8e5f3; display: flex; justify-content: space-between; align-items: center; font-size: 16px; font-weight: 800; color: #132238; }
        .billing-summary-empty { font-size: 13px; color: #64748b; }
        .onboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 8px; }
        .panel-card { border: 1px solid #d8e5f3; border-radius: 14px; padding: 22px; background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%); transition: all 0.2s ease; }
        .panel-card:hover { box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06); border-color: #bfd5ef; }
        .panel-card .form-group:last-child { margin-bottom: 0; }
        .full-width-card { margin-top: 14px; }
        .section-kicker { display:inline-flex; align-items:center; gap:8px; font-size:11px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:#0f4b8f; margin-bottom:10px; }
        .section-title { font-size: 21px; font-weight: 800; color: #132238; margin: 0 0 8px; }
        .section-intro { margin: 0 0 18px; font-size: 14px; line-height: 1.55; color: #526277; max-width: 760px; }
        .helper-copy { color:#64748b; display:block; margin-top:6px; line-height:1.45; }
        .step1-actionbar { margin-top: 28px; display:flex; justify-content:flex-end; }
        #step1-next-btn { min-width: 250px; box-shadow: 0 10px 24px rgba(15, 75, 143, 0.16); }

        #result { margin-top: 20px; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .otp-panel { border: 1px solid #d9e2ef; border-radius: 8px; padding: 14px; margin-top: 10px; background: #f8f9ff; }
        .otp-inline { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-top: 10px; }
        .otp-status { font-size: 13px; color: #475569; margin-top: 8px; }
        .otp-status.ok { color: #15803d; }
        .otp-status.err { color: #b91c1c; }
        .otp-channel-label { display:block; font-weight:700; color:#1e293b; margin-bottom:8px; }
        .otp-consent-copy { color:#475569; display:block; margin-top:8px; line-height:1.45; }
        .field-status { font-size: 12px; margin-top: 6px; color: #475569; }
        .field-status.ok { color: #15803d; font-weight: 600; }
        .field-status.err { color: #b91c1c; }
        .agreement-link {
            color: #0f4b8f;
            text-decoration: underline;
            cursor: pointer;
        }
        .agreement-note {
            margin-top: 8px;
            font-size: 12px;
            color: #64748b;
        }
        .agreement-receipt-actions {
            display: none;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin: 10px 0 0 24px;
        }
        .agreement-receipt-btn {
            appearance: none;
            border: 1px solid #c7d7ec;
            background: #fff;
            color: #0f4b8f;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 34px;
            line-height: 1.1;
            vertical-align: middle;
        }
        .agreement-receipt-btn:hover {
            background: #eff6ff;
        }
        .agreement-modal {
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.18), rgba(59, 130, 246, 0) 34%),
                rgba(15, 23, 42, 0.58);
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
            backdrop-filter: blur(8px);
        }
        .agreement-modal.show {
            display: flex;
        }
        .agreement-modal-panel {
            width: min(1180px, 98vw);
            height: min(880px, 96vh);
            background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
            border-radius: 18px;
            border: 1px solid rgba(191, 213, 239, 0.9);
            box-shadow: 0 30px 80px rgba(2, 6, 23, 0.34);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .agreement-modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            background: transparent;
            color: #0f172a;
            padding: 18px 20px 14px;
            border-bottom: 1px solid #dbe7f5;
        }
        .agreement-modal-headline {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .agreement-modal-title {
            font-size: 22px;
            line-height: 1.15;
            font-weight: 800;
            color: #0f4b8f;
        }
        .agreement-modal-subtitle {
            font-size: 13px;
            color: #526277;
        }
        .agreement-modal-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .agreement-close-btn {
            border: 1px solid #c7d7ec;
            background: #fff;
            color: #0f4b8f;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }
        .agreement-close-btn:hover {
            background: #eff6ff;
        }
        .agreement-frame {
            border: 0;
            width: 100%;
            height: 100%;
            flex: 1 1 auto;
            background: #fff;
            border-top: 1px solid rgba(255,255,255,0.7);
        }
        @media (max-width: 900px) {
            .onboard-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="wizard-container">
        <div class="wizard-header">
            <div style="font-size: 2rem; font-weight: 800; color: #0f4b8f; margin-bottom: 15px;">MedEx</div>
            <h2><?php echo xlt("Practice Onboarding"); ?></h2>
        </div>

        <div class="wizard-steps">
            <div id="wizard-progress-fill" class="wizard-progress-fill"></div>
            <div class="step <?php echo $step == 1 ? 'active' : 'completed'; ?>">1
                <div class="step-label"><?php echo xlt("Register"); ?></div>
            </div>
            <div class="step <?php echo $step == 2 ? 'active' : ($step > 2 ? 'completed' : ''); ?>">2
                <div class="step-label"><?php echo xlt("Configure"); ?></div>
            </div>
            <div class="step <?php echo $step == 3 ? 'active' : ''; ?>">3
                <div class="step-label"><?php echo xlt("Activate"); ?></div>
            </div>
        </div>

        <?php if ($step == 1): ?>
            <!-- Step 1: Account Registration -->
            <form id="form-step-1">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>" />
                <div class="panel-card full-width-card">
                        <div class="section-kicker"><?php echo xlt("Step 1"); ?></div>
                        <h3 class="section-title"><?php echo xlt("Create the administrator account"); ?></h3>
                        <p class="section-intro">
                            <?php echo xlt("Enter the email and password that will own this MedEx setup."); ?><br>
                            <?php echo xlt("This account will be used to complete onboarding and manage services later."); ?>
                        </p>
                        <p style="margin:0 0 18px; font-size:14px; color:#526277;">
                            <?php echo xlt("Already have a MedEx account?"); ?>
                            <a href="reconnect.php?site=<?php echo attr_url($siteId); ?>" style="font-weight:700; color:#0f4b8f; text-decoration:underline;">
                                <?php echo xlt("Reconnect."); ?>
                            </a>
                        </p>
                        <div class="form-group">
                            <label for="email"><?php echo xlt("Administrator E-mail"); ?></label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="admin@practice.com" required>
                            <small class="helper-copy"><?php echo xlt("This email will be your username for signing in to MedEx."); ?></small>
                            <div id="email-error" class="field-error"><?php echo xlt("Please enter a valid administrator email address."); ?></div>
                        </div>
                        <div class="form-group">
                            <label for="password"><?php echo xlt("Password"); ?></label>
                            <div class="password-wrap">
                                <input type="password" id="password" name="password" class="form-control" required style="padding-right:40px;">
                                <i class="fa fa-eye-slash password-toggle" id="toggle-password" title="<?php echo xla("Show/Hide Password"); ?>"></i>
                            </div>
                            <small class="helper-copy"><?php echo xlt("Use at least 8 characters with uppercase, lowercase, number, and special character."); ?></small>
                            <div id="password-error" class="field-error"><?php echo xlt("Password must be at least 8 characters and include uppercase, lowercase, number, and special character."); ?></div>
                        </div>
                        <div class="form-group">
                            <label for="rpassword"><?php echo xlt("Confirm"); ?></label>
                            <div class="password-wrap">
                                <input type="password" id="rpassword" name="rpassword" class="form-control" required style="padding-right:40px;">
                                <i class="fa fa-eye-slash password-toggle" id="toggle-rpassword" title="<?php echo xla("Show/Hide Password"); ?>"></i>
                            </div>
                            <div id="rpassword-error" class="field-error"><?php echo xlt("Confirm password must match the password."); ?></div>
                            <div id="rpassword-ok" class="field-status ok" style="display:none;">
                                <i class="fa fa-check-circle" aria-hidden="true"></i>
                                <?php echo xlt("Passwords match."); ?>
                            </div>
                        </div>
                        <input type="hidden" id="callback_url" name="callback_url" value="<?php echo attr($defaultOpenEmrUrl); ?>">
                </div>

                <div class="panel-card full-width-card">
                    <div class="section-kicker"><?php echo xlt("Verify Identity"); ?></div>
                    <h3 class="section-title"><?php echo xlt("Send One-Time Passcode"); ?></h3>
                    <p class="section-intro"><?php echo xlt("Verify the administrator before we continue. Choose the delivery method, send the code, and confirm it here."); ?></p>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="otp_channel" class="otp-channel-label"><?php echo xlt("Delivery Method"); ?></label>
                        <select id="otp_channel" name="otp_channel" class="form-control">
                            <option value="sms"><?php echo xlt("SMS One-Time Password (OTP)"); ?></option>
                            <option value="email"><?php echo xlt("Email One-Time Password (OTP)"); ?></option>
                        </select>
                        <small class="otp-consent-copy">
                            <span id="otp-consent-copy"><?php echo xlt("You agree to allow us to send you this email to verify your identity before enabling your MedEx setup."); ?></span>
                            <a href="<?php echo attr($privacyUrl); ?>" target="_blank" rel="noopener noreferrer"><?php echo xlt("Privacy Policy"); ?></a>
                        </small>
                        <?php // SMS/WhatsApp OTP intentionally hidden in UI until end-to-end destination + verification flow is implemented. ?>
                        <div id="otp-sms-destination-wrap" class="form-group" style="display:none; margin-top: 10px;">
                            <label for="otp_sms_destination"><?php echo xlt("Mobile Number for SMS OTP"); ?></label>
                            <input type="tel" id="otp_sms_destination" name="otp_sms_destination" class="form-control"
                                   placeholder="+15551234567">
                            <small style="color:#64748b; display:block; margin-top:4px;"><?php echo xlt("SMS OTP currently supports U.S./Canada numbers only."); ?></small>
                            <div id="otp-sms-error" class="field-error"><?php echo xlt("Enter a valid U.S./Canada mobile number (for example 555-123-4567 or +1 555 123 4567)."); ?></div>
                        </div>
                    </div>
                    <div class="otp-panel" style="margin-top: 0;">
                        <div id="otp-send-row" class="otp-inline">
                            <button type="button" class="btn btn-primary" id="send-otp-btn"><?php echo xlt("Send SMS Code"); ?></button>
                        </div>
                        <div id="otp-verify-row" class="otp-inline">
                            <input type="text" id="otp_code" class="form-control" style="max-width: 220px;" placeholder="<?php echo xla("Enter 6-digit code"); ?>" maxlength="6">
                            <button type="button" class="btn" style="background:#e2e8f0;" id="verify-otp-btn"><?php echo xlt("Verify Code"); ?></button>
                        </div>
                        <div id="otp-status" class="otp-status"><?php echo xlt("Send and verify your one-time password before continuing."); ?></div>
                        <input type="hidden" id="otp_proof" name="otp_proof" value="">
                    </div>
                </div>

                <div class="panel-card full-width-card">
                    <div class="section-kicker"><?php echo xlt("Required"); ?></div>
                    <h3 class="section-title"><?php echo xlt("Required Agreements"); ?></h3>
                    <p class="section-intro"><?php echo xlt("Open each agreement, complete the signature flow, and then the checkbox will unlock automatically."); ?></p>
                    <input type="hidden" id="terms_completed" name="terms_completed" value="0">
                    <input type="hidden" id="baa_completed" name="baa_completed" value="0">
                    <input type="hidden" id="terms_signature_name" name="terms_signature_name" value="">
                    <input type="hidden" id="terms_signer_title" name="terms_signer_title" value="">
                    <input type="hidden" id="terms_signed_at" name="terms_signed_at" value="">
                    <input type="hidden" id="terms_agreement_version" name="terms_agreement_version" value="<?php echo attr($termsVersion); ?>">
                    <input type="hidden" id="terms_practice_name" name="terms_practice_name" value="">
                    <input type="hidden" id="terms_legal_corporate_name" name="terms_legal_corporate_name" value="">
                    <input type="hidden" id="baa_signature_name" name="baa_signature_name" value="">
                    <input type="hidden" id="baa_signer_title" name="baa_signer_title" value="">
                    <input type="hidden" id="baa_signed_at" name="baa_signed_at" value="">
                    <input type="hidden" id="baa_agreement_version" name="baa_agreement_version" value="<?php echo attr($baaVersion); ?>">
                    <input type="hidden" id="baa_practice_name" name="baa_practice_name" value="">
                    <input type="hidden" id="baa_legal_corporate_name" name="baa_legal_corporate_name" value="">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label style="font-weight:400; margin-bottom: 0;">
                            <input type="checkbox" id="TERMS_yes" name="TERMS_yes" value="1" required disabled>
                            <?php echo xlt("I have read and my practice agrees to the"); ?>
                            <a href="<?php echo attr($termsUrl); ?>" id="open-terms-link" class="agreement-link">
                                <?php echo xlt("MedEx Terms and Conditions"); ?>
                            </a>
                            (<?php echo xlt("Version"); ?> <?php echo text($termsVersion); ?>)
                        </label>
                        <div id="terms-receipt-actions" class="agreement-receipt-actions">
                            <button type="button" id="terms-view-btn" class="agreement-receipt-btn"><i class="fa fa-eye" aria-hidden="true"></i><?php echo xlt("View"); ?></button>
                            <button type="button" id="terms-download-btn" class="agreement-receipt-btn"><i class="fa fa-file-pdf-o" aria-hidden="true"></i><?php echo xlt("Download PDF"); ?></button>
                            <button type="button" id="terms-edit-btn" class="agreement-receipt-btn"><i class="fa fa-pencil" aria-hidden="true"></i><?php echo xlt("Edit"); ?></button>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-weight:400; margin-bottom: 0;">
                            <input type="checkbox" id="BusAgree_yes" name="BusAgree_yes" value="1" required disabled>
                            <?php echo xlt("I have read and accept the"); ?>
                            <a href="<?php echo attr($baaUrl); ?>" id="open-baa-link" class="agreement-link">
                                <?php echo xlt("MedEx Business Associate Agreement (BAA)"); ?>
                            </a>
                            (<?php echo xlt("Version"); ?> <?php echo text($baaVersion); ?>)
                        </label>
                        <div id="baa-receipt-actions" class="agreement-receipt-actions">
                            <button type="button" id="baa-view-btn" class="agreement-receipt-btn"><i class="fa fa-eye" aria-hidden="true"></i><?php echo xlt("View"); ?></button>
                            <button type="button" id="baa-download-btn" class="agreement-receipt-btn"><i class="fa fa-file-pdf-o" aria-hidden="true"></i><?php echo xlt("Download PDF"); ?></button>
                            <button type="button" id="baa-edit-btn" class="agreement-receipt-btn"><i class="fa fa-pencil" aria-hidden="true"></i><?php echo xlt("Edit"); ?></button>
                        </div>
                    </div>
                    <div class="agreement-note">
                        <?php echo xlt("Open each agreement link and complete electronic signature to unlock its checkbox."); ?>
                    </div>
                </div>
                <div class="step1-actionbar">
                    <button type="button" id="step1-next-btn" class="btn btn-primary" onclick="submitStep1()" disabled><?php echo xlt("Next: Configure Services"); ?> <i class="fa fa-arrow-right"></i></button>
                </div>
            </form>

            <div id="agreement-modal" class="agreement-modal" aria-hidden="true">
                <div class="agreement-modal-panel">
                    <div class="agreement-modal-head">
                        <div class="agreement-modal-headline">
                            <div id="agreement-modal-title" class="agreement-modal-title"><?php echo xlt("Agreement"); ?></div>
                            <div class="agreement-modal-subtitle"><?php echo xlt("Review the agreement, complete the signature fields, and return to onboarding automatically."); ?></div>
                        </div>
                        <div class="agreement-modal-actions">
                            <button type="button" id="agreement-close-btn" class="agreement-close-btn">
                                <?php echo xlt("Close"); ?>
                            </button>
                        </div>
                    </div>
                    <iframe id="agreement-frame" class="agreement-frame" title="<?php echo xla("Agreement"); ?>"></iframe>
                </div>
            </div>

        <?php elseif ($step == 2): ?>
            <!-- Step 2: Service Configuration -->
            <form id="form-step-2">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>" />
                <?php
                $formatUnit = static function ($rawUnit, string $fallback): string {
                    $unit = ltrim(trim((string)$rawUnit), '/');
                    if ($unit === '') {
                        $unit = $fallback;
                    }
                    return '/' . $unit;
                };
                ?>
                <p><?php echo xlt("Select the services you wish to enable for your practice. You can start with a trial for any provider-based service."); ?></p>
                <?php if (!$hasAvailableServices): ?>
                <div class="alert alert-danger">
                    <?php echo xlt("No MedEx services are currently available for this account."); ?>
                </div>
                <?php endif; ?>
                <?php foreach ($onboardingServices as $service): ?>
                    <?php
                    $serviceKey = (string)($service['key'] ?? '');
                    $serviceSlug = (string)($service['slug'] ?? '');
                    $serviceTitle = (string)($service['title'] ?? $serviceKey);
                    $servicePrice = isset($service['price']) ? (float)$service['price'] : 0.0;
                    $serviceUnit = (string)($service['unit'] ?? '');
                    $providerBased = !empty($service['provider_based']);
                    $selectors = is_array($service['selectors'] ?? null) ? $service['selectors'] : [];
                    $requiresProviders = !empty($selectors['providers']);
                    $requiresFacilities = !empty($selectors['facilities']);
                    $serviceHelpUrl = (string)($service['help_url'] ?? '');
                    $serviceDescription = (string)($service['description'] ?? '');
                    ?>
                <div class="service-card" data-service-key="<?php echo attr($serviceKey); ?>" data-service-label="<?php echo attr($serviceTitle); ?>" data-service-price="<?php echo attr((string)$servicePrice); ?>" data-provider-based="<?php echo attr($providerBased ? '1' : '0'); ?>" data-requires-provider="<?php echo attr($requiresProviders ? '1' : '0'); ?>" data-requires-facility="<?php echo attr($requiresFacilities ? '1' : '0'); ?>">
                    <input type="checkbox" name="selected_services[]" value="<?php echo attr($serviceKey); ?>" id="service_<?php echo attr($serviceSlug); ?>">
                    <div class="service-info">
                        <div class="service-title-row">
                            <div class="service-title"><?php echo text($serviceTitle); ?></div>
                            <?php if ($serviceHelpUrl !== ''): ?>
                            <a class="service-help-link" href="<?php echo attr($serviceHelpUrl); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo attr($serviceTitle . ' ' . xlt('Help')); ?>" aria-label="<?php echo attr($serviceTitle . ' ' . xlt('Help')); ?>">
                                <i class="fa fa-question" aria-hidden="true"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="service-desc"><?php echo text($serviceDescription); ?></div>
                        <div class="service-price">
                            <?php echo "$" . number_format($servicePrice, 2) . " " . text($formatUnit($serviceUnit, $providerBased ? 'mo/per provider' : 'mo')); ?>
                        </div>
                        <?php if ($requiresProviders || $requiresFacilities): ?>
                        <div class="service-config-panel" data-config-for="<?php echo attr($serviceKey); ?>">
                            <p class="service-config-title"><?php echo xlt("Configure Service"); ?></p>
                            <p class="service-config-copy"><?php echo xlt("Choose the OpenEMR records this service should include before it is added to the cart."); ?></p>
                            <div class="service-config-grid">
                                <?php if ($requiresProviders): ?>
                                <div class="service-config-group">
                                    <label><?php echo xlt("Providers"); ?></label>
                                    <div class="selection-toolbar">
                                        <span class="selection-toolbar-copy"><?php echo xlt("Select the providers included for this service."); ?></span>
                                        <div class="selection-toolbar-actions">
                                            <button type="button" class="selection-link-btn" data-service-key="<?php echo attr($serviceKey); ?>" data-select-group="providers" data-select-action="all"><?php echo xlt("Select all"); ?></button>
                                            <button type="button" class="selection-link-btn" data-service-key="<?php echo attr($serviceKey); ?>" data-select-group="providers" data-select-action="none"><?php echo xlt("Deselect"); ?></button>
                                        </div>
                                    </div>
                                    <div class="provider-list">
                                        <?php foreach ($providerRows as $row): ?>
                                            <div class="provider-item">
                                                <input type="checkbox" name="service_config[<?php echo attr($serviceKey); ?>][providers][]" value="<?php echo attr((string)($row['id'] ?? '')); ?>">
                                                <?php echo text(trim((string)($row['lname'] ?? '') . ', ' . (string)($row['fname'] ?? ''))); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if ($requiresFacilities): ?>
                                <div class="service-config-group">
                                    <label><?php echo xlt("Facilities"); ?></label>
                                    <div class="selection-toolbar">
                                        <span class="selection-toolbar-copy"><?php echo xlt("Select the facilities included for this service."); ?></span>
                                        <div class="selection-toolbar-actions">
                                            <button type="button" class="selection-link-btn" data-service-key="<?php echo attr($serviceKey); ?>" data-select-group="facilities" data-select-action="all"><?php echo xlt("Select all"); ?></button>
                                            <button type="button" class="selection-link-btn" data-service-key="<?php echo attr($serviceKey); ?>" data-select-group="facilities" data-select-action="none"><?php echo xlt("Deselect"); ?></button>
                                        </div>
                                    </div>
                                    <div class="provider-list">
                                        <?php foreach ($facilityRows as $facility): ?>
                                            <div class="provider-item">
                                                <input type="checkbox" name="service_config[<?php echo attr($serviceKey); ?>][facilities][]" value="<?php echo attr((string)($facility['id'] ?? '')); ?>">
                                                <?php echo text((string)($facility['name'] ?? '')); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="service-config-note"><?php echo xlt("Complete the required selections before continuing to activation."); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="billing-summary-card">
                    <h4 class="billing-summary-title"><?php echo xlt("Billing Summary"); ?></h4>
                    <p class="billing-summary-copy"><?php echo xlt("This shows what will be added to the cart based on the services and provider counts you choose here."); ?></p>
                    <div id="billing-summary-lines" class="billing-summary-empty"><?php echo xlt("No services selected yet."); ?></div>
                    <div class="billing-summary-total">
                        <span><?php echo xlt("Estimated Monthly Total"); ?></span>
                        <span id="billing-summary-total">$0.00</span>
                    </div>
                </div>

                <div style="margin-top: 30px; display: flex; justify-content: space-between;">
                    <button type="button" class="btn" style="background: #eee;" onclick="location.href='onboarding.php?step=1&site=<?php echo attr_js($siteId); ?>'"><?php echo xlt("Back"); ?></button>
                    <?php if ($hasAvailableServices): ?>
                        <button type="button" class="btn btn-primary" onclick="submitStep2()"><?php echo xlt("Next: Activation & Payment"); ?> <i class="fa fa-arrow-right"></i></button>
                    <?php endif; ?>
                </div>
            </form>

        <?php elseif ($step == 3): ?>
            <!-- Step 3: Payment & Activation -->
            <div>
                <h3><?php echo xlt("Review & Payment"); ?></h3>
                <p style="color: #666;">
                    <?php echo xlt("Review your selected services, adjust them if needed, and activate using the same subscription processor used elsewhere in MedEx."); ?>
                </p>

                <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <strong><?php echo xlt("Subscription Summary"); ?>:</strong>
                    <ul id="summary-list" style="margin-top: 10px;">
                    </ul>
                    <div style="border-top: 2px solid #ddd; margin-top: 15px; padding-top: 15px;">
                        <strong><?php echo xlt("Total"); ?>:</strong> <span id="cart-total" style="font-size: 20px; color: #0f4b8f;">$0.00</span>
                    </div>
                </div>

                <div id="payment-errors" class="alert alert-danger" style="display: none; margin-top: 15px;"></div>

                <div id="payment-section" style="display:none; margin: 30px 0;">
                    <h4 id="payment-section-title"><?php echo xlt("Payment Information"); ?></h4>
                    <div style="border: 1px solid #d0d7de; border-radius: 8px; padding: 16px; background: #fff;">
                        <div class="form-group">
                            <label for="medex-cardholder-name"><?php echo xlt("Cardholder Name"); ?></label>
                            <input type="text" id="medex-cardholder-name" class="form-control" autocomplete="cc-name">
                        </div>
                        <div class="form-group">
                            <label><?php echo xlt("Card Number"); ?></label>
                            <div id="medex-card-number" style="height: 45px; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background:#fff;"></div>
                        </div>
                        <div style="display:flex; gap:15px;">
                            <div class="form-group" style="flex:1;">
                                <label><?php echo xlt("Expiration Date"); ?></label>
                                <div id="medex-card-expiration" style="height: 45px; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background:#fff;"></div>
                            </div>
                            <div class="form-group" style="flex:1;">
                                <label><?php echo xlt("CVV"); ?></label>
                                <div id="medex-card-cvv" style="height: 45px; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background:#fff;"></div>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label><?php echo xlt("Postal Code"); ?></label>
                            <div id="medex-card-postal" style="height: 45px; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background:#fff;"></div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" id="payment-submit-btn" style="margin-top: 18px;">
                        <i class="fa fa-lock"></i> <?php echo xlt("Complete Payment & Activate"); ?>
                    </button>
                </div>

                <div style="margin-top: 30px; display: flex; justify-content: space-between;">
                    <button type="button" class="btn" id="edit-services-btn" style="background: #eee;"><?php echo xlt("Edit Services"); ?></button>
                    <button type="button" class="btn btn-primary" id="review-changes-btn">
                        <i class="fa fa-check"></i> <?php echo xlt("Activate Services"); ?>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <div id="result"></div>
    </div>

    <script>
        let otpVerified = false;
        let otpStatusRequest = 0;
        const onboardingVerificationWindowSeconds = <?php echo (int)$onboardingVerificationWindowSeconds; ?>;
        const wizardStep = <?php echo (int)$step; ?>;
        const onboardingDraftStorageKey = 'medex_onboarding_draft';
        const onboardingServiceDefinitions = <?php echo json_encode($onboardingServices); ?> || {};
        const onboardingServiceLabels = <?php echo json_encode($serviceLabels); ?> || {};
        const onboardingServerCart = <?php echo json_encode(['items' => $sessionCartItems, 'total' => $sessionCartTotal]); ?>;
        window.braintreeToken = <?php echo json_encode($braintreeToken ?? null); ?>;
        window._medexPayment = window._medexPayment || {
            token: null,
            clientInstance: null,
            hostedFieldsInstance: null,
            ready: false
        };

        function togglePasswordField(inputSelector, iconSelector) {
            const input = $(inputSelector);
            const icon = $(iconSelector);
            const isPassword = input.attr('type') === 'password';
            input.attr('type', isPassword ? 'text' : 'password');
            icon.toggleClass('fa-eye fa-eye-slash');
        }

        function setOtpStatus(message, kind = '') {
            const el = $("#otp-status");
            el.removeClass('ok err');
            if (kind) {
                el.addClass(kind);
            }
            el.text(message);
        }

        function updateOtpUiState() {
            const controlsVisible = !otpVerified;
            $("#otp-send-row").toggle(controlsVisible);
            $("#otp-verify-row").toggle(controlsVisible);
        }

        function ensureActiveSession() {
            if (typeof top !== "undefined" && typeof top.restoreSession === "function") {
                top.restoreSession();
            }
        }

        function ajaxErrorMessage(jqXHR, fallbackMessage) {
            const body = (jqXHR && typeof jqXHR.responseText === "string") ? jqXHR.responseText : "";
            if (body.indexOf("login_screen.php?error=1") !== -1 || body.indexOf("timed_out = true") !== -1) {
                return "Your session timed out. Please log in again and retry.";
            }
            if (jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.error) {
                return jqXHR.responseJSON.error;
            }
            return fallbackMessage;
        }

        function resetLocalOtpVerification(message = "Send and verify your one-time password before continuing.") {
            otpVerified = false;
            $("#otp_proof").val("");
            $("#otp_code").val("");
            setOtpStatus(message);
            updateOtpUiState();
        }

        function getCurrentOtpIdentity() {
            const channel = $("#otp_channel").val();
            const email = ($("#email").val() || "").trim();
            const sms = (channel === "sms") ? normalizeSmsForE164($("#otp_sms_destination").val() || "") : "";
            const destination = (channel === "sms") ? sms : email;
            return { channel, email, sms, destination };
        }

        function restoreOtpStatusForCurrentIdentity() {
            const identity = getCurrentOtpIdentity();
            if (!validateOtpDestination(identity.channel, identity.email, identity.sms)) {
                return;
            }

            const requestId = ++otpStatusRequest;
            ensureActiveSession();
            $.ajax({
                url: 'onboarding_otp.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    csrf_token_form: $('input[name="csrf_token_form"]').val(),
                    action: 'status',
                    otp_channel: identity.channel,
                    email: identity.email,
                    otp_sms_destination: identity.sms
                },
                success: function(response) {
                    if (requestId !== otpStatusRequest) {
                        return;
                    }
                    if (response.success && response.verified && response.otp_proof) {
                        otpVerified = true;
                        $("#otp_proof").val(response.otp_proof);
                        setOtpStatus(response.message || "One-time password already verified.", "ok");
                        updateOtpUiState();
                    }
                    updateStep1SubmitState();
                }
            });
        }

        function updateOtpDestinationVisibility() {
            const channel = $("#otp_channel").val();
            if (channel === "sms") {
                $("#otp-sms-destination-wrap").show();
            } else {
                $("#otp-sms-destination-wrap").hide();
                $("#otp_sms_destination").val("");
                clearFieldError("#otp_sms_destination", "#otp-sms-error");
            }
            resetLocalOtpVerification();
            updateOtpConsentCopy();
            restoreOtpStatusForCurrentIdentity();
            updateStep1SubmitState();
        }

        function updateOtpConsentCopy() {
            const isSms = $("#otp_channel").val() === "sms";
            const channel = isSms ? "text message" : "email";
            $("#otp-consent-copy").text(
                "You agree to allow us to send you this " + channel + " to verify your identity before enabling your MedEx setup. "
            );
            $("#send-otp-btn").text(isSms ? "Send SMS Code" : "Send Email Code");
        }

        function normalizeSmsForE164(raw) {
            let value = (raw || "").trim();
            if (!value) {
                return "";
            }
            value = value.replace(/(ext|x)\s*\d+$/i, "").trim();
            if (value.startsWith("00")) {
                value = "+" + value.slice(2);
            }
            if (value.startsWith("+")) {
                const digits = value.slice(1).replace(/\D/g, "");
                return digits ? ("+" + digits) : "";
            }
            const digitsOnly = value.replace(/\D/g, "");
            if (digitsOnly.length === 10) {
                return "+1" + digitsOnly;
            }
            if (digitsOnly.length === 11 && digitsOnly.startsWith("1")) {
                return "+" + digitsOnly;
            }
            return "";
        }

        function validateSmsField(showMessage = true) {
            const normalized = normalizeSmsForE164($("#otp_sms_destination").val() || "");
            if (/^\+1\d{10}$/.test(normalized)) {
                $("#otp_sms_destination").val(normalized);
                clearFieldError("#otp_sms_destination", "#otp-sms-error");
                return normalized;
            }
            if (showMessage) {
                setFieldError(
                    "#otp_sms_destination",
                    "#otp-sms-error",
                    "Enter a valid U.S./Canada mobile number (for example 555-123-4567 or +1 555 123 4567)."
                );
            }
            return "";
        }

        function validateOtpDestination(channel, email, sms) {
            if (channel === "email") {
                const emailValue = (email || "").trim();
                const emailInput = document.getElementById("email");
                if (!emailValue) {
                    return false;
                }
                if (emailInput && typeof emailInput.checkValidity === "function") {
                    return emailInput.checkValidity();
                }
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue);
            }
            if (channel === "sms") {
                return !!validateSmsField(true);
            }
            return false;
        }

        function setFieldError(inputSelector, errorSelector, message) {
            const input = $(inputSelector);
            const error = $(errorSelector);
            input.addClass('invalid');
            error.text(message).show();
        }

        function clearFieldError(inputSelector, errorSelector) {
            $(inputSelector).removeClass('invalid');
            $(errorSelector).hide();
        }

        function setFieldSuccess(selector) {
            $(selector).css("display", "block");
        }

        function clearFieldSuccess(selector) {
            $(selector).hide();
        }

        function validateEmailField(showMessage = true) {
            const emailInput = document.getElementById("email");
            const emailValue = (emailInput && emailInput.value ? emailInput.value : "").trim();
            const isValid = !!emailValue && emailInput && emailInput.checkValidity();

            if (isValid) {
                clearFieldError("#email", "#email-error");
                return true;
            }

            if (showMessage) {
                setFieldError("#email", "#email-error", "Please enter a valid administrator email address.");
            }
            return false;
        }

        function validatePasswordField(showMessage = true) {
            const passwordValue = ($("#password").val() || "").trim();
            const strongPassword = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;
            const isValid = strongPassword.test(passwordValue);
            if (isValid) {
                clearFieldError("#password", "#password-error");
                return true;
            }
            if (showMessage) {
                setFieldError(
                    "#password",
                    "#password-error",
                    "Password must be at least 8 characters and include uppercase, lowercase, number, and special character."
                );
            }
            return false;
        }

        function validateConfirmPasswordField(showMessage = true) {
            const password = ($("#password").val() || "");
            const confirm = ($("#rpassword").val() || "");
            const matches = confirm.length > 0 && password === confirm;
            if (matches) {
                clearFieldError("#rpassword", "#rpassword-error");
                setFieldSuccess("#rpassword-ok");
                return true;
            }
            clearFieldSuccess("#rpassword-ok");
            if (showMessage && confirm.length > 0) {
                setFieldError("#rpassword", "#rpassword-error", "Confirm password must match the password.");
            }
            return false;
        }

        function isTermsCompleted() {
            if ($("#terms_completed").val() !== "1") {
                return false;
            }
            return isAgreementStillFresh($("#terms_signed_at").val());
        }

        function isBaaCompleted() {
            if ($("#baa_completed").val() !== "1") {
                return false;
            }
            return isAgreementStillFresh($("#baa_signed_at").val());
        }

        function isAgreementStillFresh(rawTimestamp) {
            const value = String(rawTimestamp || "").trim();
            if (!value) {
                return false;
            }
            const signedMs = Date.parse(value);
            if (!Number.isFinite(signedMs) || signedMs <= 0) {
                return false;
            }
            return (Date.now() - signedMs) <= (onboardingVerificationWindowSeconds * 1000);
        }

        function syncAgreementCheckboxState() {
            const termsDone = isTermsCompleted();
            const baaDone = isBaaCompleted();
            $("#TERMS_yes").prop("disabled", !termsDone);
            $("#BusAgree_yes").prop("disabled", !baaDone);
            if (!termsDone) {
                $("#TERMS_yes").prop("checked", false);
            }
            if (!baaDone) {
                $("#BusAgree_yes").prop("checked", false);
            }
        }

        function updateStep1SubmitState() {
            const accountReady = validateEmailField(false) &&
                validatePasswordField(false) &&
                validateConfirmPasswordField(false);
            const agreementsReady = isTermsCompleted() &&
                isBaaCompleted() &&
                $("#TERMS_yes").is(':checked') &&
                $("#BusAgree_yes").is(':checked');
            const canSubmit = accountReady && otpVerified && agreementsReady;
            $("#step1-next-btn").prop("disabled", !canSubmit);
            updateStep1Progress(accountReady, otpVerified, agreementsReady);
        }

        function updateStep1Progress(accountReady, otpReady, agreementsReady) {
            const completed = (accountReady ? 1 : 0) + (otpReady ? 1 : 0) + (agreementsReady ? 1 : 0);
            const pct = Math.round((completed / 3) * 50);
            $("#wizard-progress-fill").css("width", pct + "%");
        }

        function updateStep2Progress() {
            const anyServiceSelected = $("input[name='selected_services[]']:checked").length > 0;
            const selectionReady = selectedServiceCards().every(function(card) {
                return validateServiceSelections(card.serviceKey, false);
            });
            const completed = (anyServiceSelected ? 1 : 0) + (selectionReady ? 1 : 0);
            const pct = 50 + Math.round((completed / 2) * 50);
            $("#wizard-progress-fill").css("width", pct + "%");
        }

        function selectedServiceCards() {
            return $("input[name='selected_services[]']:checked").map(function() {
                const $card = $(this).closest('.service-card');
                return {
                    element: $card,
                    serviceKey: String($card.data('serviceKey') || ''),
                    label: String($card.data('serviceLabel') || $card.data('serviceKey') || ''),
                    price: parseFloat($card.data('servicePrice') || 0),
                    providerBased: String($card.data('providerBased') || '0') === '1',
                    requiresProviders: String($card.data('requiresProvider') || '0') === '1',
                    requiresFacilities: String($card.data('requiresFacility') || '0') === '1'
                };
            }).get().filter(function(card) {
                return !!card.serviceKey;
            });
        }

        function configSelector(serviceKey, group) {
            return "input[name='service_config[" + serviceKey.replace(/'/g, "\\'") + "][" + group + "][]']";
        }

        function getServiceSelectionValues(serviceKey, group) {
            return $(configSelector(serviceKey, group)).filter(':checked').map(function() {
                return parseInt($(this).val(), 10);
            }).get().filter(Number.isFinite);
        }

        function validateServiceSelections(serviceKey, showMessage) {
            const definition = onboardingServiceDefinitions[serviceKey] || {};
            const requiresProviders = !!(definition.selectors && definition.selectors.providers);
            const requiresFacilities = !!(definition.selectors && definition.selectors.facilities);
            const providerIds = requiresProviders ? getServiceSelectionValues(serviceKey, 'providers') : [];
            const facilityIds = requiresFacilities ? getServiceSelectionValues(serviceKey, 'facilities') : [];

            if (requiresProviders && providerIds.length < 1) {
                if (showMessage) {
                    const label = String(definition.title || onboardingServiceLabels[serviceKey] || serviceKey);
                    $("#result").html('<div class="alert alert-danger">Select at least one provider for ' + $('<div>').text(label).html() + '.</div>');
                }
                return false;
            }
            if (requiresFacilities && facilityIds.length < 1) {
                if (showMessage) {
                    const label = String(definition.title || onboardingServiceLabels[serviceKey] || serviceKey);
                    $("#result").html('<div class="alert alert-danger">Select at least one facility for ' + $('<div>').text(label).html() + '.</div>');
                }
                return false;
            }
            return true;
        }

        function syncServiceConfigPanels() {
            $(".service-config-panel[data-config-for]").each(function() {
                const serviceKey = String($(this).attr("data-config-for") || '');
                const selected = $("#form-step-2 input[name='selected_services[]'][value='" + serviceKey.replace(/'/g, "\\'") + "']").is(":checked");
                $(this).toggleClass("show", selected);
            });
            updateBillingSummary();
        }

        function updateBillingSummary() {
            const summaryLines = [];
            let total = 0;

            function pushLine(card, quantity, meta) {
                const serviceKey = card.serviceKey;
                const serviceLabel = card.label || serviceKey;
                const unitPrice = card.price;
                const providerBased = card.providerBased;
                const effectiveQty = providerBased ? Math.max(quantity, 0) : (quantity > 0 ? quantity : 1);
                const lineTotal = providerBased ? unitPrice * effectiveQty : (quantity > 0 ? unitPrice : 0);
                total += lineTotal;
                summaryLines.push(
                    '<div class="billing-summary-item">' +
                        '<div>' +
                            '<div class="billing-summary-item-name">' + $('<div>').text(serviceLabel || serviceKey).html() + '</div>' +
                            (meta ? '<div class="billing-summary-item-meta">' + $('<div>').text(meta).html() + '</div>' : '') +
                        '</div>' +
                        '<div class="billing-summary-item-amount">$' + lineTotal.toFixed(2) + '</div>' +
                    '</div>'
                );
            }

            selectedServiceCards().forEach(function(card) {
                const providerIds = card.requiresProviders ? getServiceSelectionValues(card.serviceKey, 'providers') : [];
                const facilityIds = card.requiresFacilities ? getServiceSelectionValues(card.serviceKey, 'facilities') : [];
                let meta = <?php echo json_encode(xl("Practice-wide service")); ?>;
                if (card.requiresProviders || card.requiresFacilities) {
                    const parts = [];
                    if (card.requiresProviders) {
                        parts.push(providerIds.length + ' <?php echo xlj("provider(s) selected"); ?>');
                    }
                    if (card.requiresFacilities) {
                        parts.push(facilityIds.length + ' <?php echo xlj("facility(ies) selected"); ?>');
                    }
                    meta = parts.join(' • ');
                }
                const quantity = card.providerBased ? providerIds.length : 1;
                if (!card.providerBased || providerIds.length > 0 || facilityIds.length > 0) {
                    pushLine(card, quantity, meta);
                }
            });

            if (summaryLines.length) {
                $("#billing-summary-lines").html('<div class="billing-summary-list">' + summaryLines.join('') + '</div>');
            } else {
                $("#billing-summary-lines").html('<div class="billing-summary-empty"><?php echo xlj("No services selected yet."); ?></div>');
            }
            $("#billing-summary-total").text('$' + total.toFixed(2));
        }

        function sendOtp() {
            const channel = $("#otp_channel").val();
            const email = ($("#email").val() || "").trim();
            const sms = (channel === "sms") ? validateSmsField(true) : "";
            const csrf = $('input[name="csrf_token_form"]').val();

            if (!validateOtpDestination(channel, email, sms)) {
                if (channel === "sms") {
                    setOtpStatus("Enter a valid SMS number in E.164 format, for example +15551234567.", "err");
                } else {
                    validateEmailField(true);
                    setOtpStatus("Enter a valid administrator email before sending OTP.", "err");
                }
                return;
            }

            otpVerified = false;
            $("#otp_proof").val("");
            $("#otp_code").val("");
            setOtpStatus("Sending one-time password...", "");
            ensureActiveSession();

            $.ajax({
                url: 'onboarding_otp.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    csrf_token_form: csrf,
                    action: 'send',
                    otp_channel: channel,
                    email: email,
                    otp_sms_destination: sms
                },
                success: function(response) {
                    if (response.success) {
                        let msg = response.message || "One-time password sent. Check your selected channel.";
                        if (response.debug_otp_code) {
                            msg += " Development OTP code: " + response.debug_otp_code;
                        }
                        setOtpStatus(msg, "ok");
                    } else {
                        setOtpStatus(response.error || "Unable to send one-time password.", "err");
                    }
                    updateStep1SubmitState();
                },
                error: function() {
                    setOtpStatus(ajaxErrorMessage(arguments[0], "Unable to send one-time password due to a request error."), "err");
                    updateStep1SubmitState();
                }
            });
        }

        function verifyOtp() {
            const code = ($("#otp_code").val() || "").trim();
            const csrf = $('input[name="csrf_token_form"]').val();
            const channel = $("#otp_channel").val();
            const email = ($("#email").val() || "").trim();
            const sms = (channel === "sms") ? validateSmsField(true) : "";

            if (!/^\d{6}$/.test(code)) {
                setOtpStatus("Enter a valid 6-digit one-time password.", "err");
                return;
            }

            setOtpStatus("Verifying one-time password...", "");
            ensureActiveSession();
            $.ajax({
                url: 'onboarding_otp.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    csrf_token_form: csrf,
                    action: 'verify',
                    otp_channel: channel,
                    email: email,
                    otp_sms_destination: sms,
                    otp_code: code
                },
                success: function(response) {
                    if (response.success) {
                        otpVerified = true;
                        $("#otp_proof").val(response.otp_proof || "");
                        setOtpStatus(response.message || "One-time password verified.", "ok");
                        updateOtpUiState();
                    } else {
                        otpVerified = false;
                        $("#otp_proof").val("");
                        setOtpStatus(response.error || "One-time password verification failed.", "err");
                        updateOtpUiState();
                    }
                    updateStep1SubmitState();
                },
                error: function() {
                    otpVerified = false;
                    $("#otp_proof").val("");
                    setOtpStatus(ajaxErrorMessage(arguments[0], "Unable to verify one-time password due to a request error."), "err");
                    updateOtpUiState();
                    updateStep1SubmitState();
                }
            });
        }

        function submitStep1() {
            const email = $("#email").val();
            const password = $("#password").val();
            const rpassword = $("#rpassword").val();
            const callbackUrl = $("#callback_url").val();
            const termsAgreed = $("#TERMS_yes").is(':checked');
            const baaAgreed = $("#BusAgree_yes").is(':checked');
            const termsCompleted = isTermsCompleted();
            const baaCompleted = isBaaCompleted();
            const otpChannel = $("#otp_channel").val();
            const otpProof = ($("#otp_proof").val() || "").trim();
            const otpSmsDestination = ($("#otp_sms_destination").val() || "").trim();
            const termsSignatureName = ($("#terms_signature_name").val() || "").trim();
            const termsSignerTitle = ($("#terms_signer_title").val() || "").trim();
            const termsSignedAt = ($("#terms_signed_at").val() || "").trim();
            const termsAgreementVersion = ($("#terms_agreement_version").val() || "").trim() || termsVersion;
            const termsPracticeName = ($("#terms_practice_name").val() || "").trim();
            const termsLegalCorporateName = ($("#terms_legal_corporate_name").val() || "").trim();
            const baaSignatureName = ($("#baa_signature_name").val() || "").trim();
            const baaSignerTitle = ($("#baa_signer_title").val() || "").trim();
            const baaSignedAt = ($("#baa_signed_at").val() || "").trim();
            const baaAgreementVersion = ($("#baa_agreement_version").val() || "").trim() || baaVersion;
            const baaPracticeName = ($("#baa_practice_name").val() || "").trim();
            const baaLegalCorporateName = ($("#baa_legal_corporate_name").val() || "").trim();

            if (!email || !password || !callbackUrl) {
                alert("Please fill all required fields");
                return;
            }
            if (!validateEmailField(true)) {
                return;
            }
            if (!validatePasswordField(true)) {
                return;
            }
            if (password !== rpassword) {
                validateConfirmPasswordField(true);
                return;
            }
            if (!termsCompleted || !termsAgreed) {
                alert("You must agree to the Terms & Conditions before signing up");
                return;
            }
            if (!termsSignatureName || !termsSignedAt) {
                alert("You must electronically sign the Terms & Conditions before continuing");
                return;
            }
            if (!termsLegalCorporateName) {
                alert("Legal corporate name is required for the Terms & Conditions signature");
                return;
            }
            if (!baaCompleted || !baaAgreed) {
                alert("You must agree to the HIPAA Business Associate Agreement before signing up");
                return;
            }
            if (!baaSignatureName || !baaSignedAt) {
                alert("You must electronically sign the HIPAA Business Associate Agreement before continuing");
                return;
            }
            if (!baaLegalCorporateName) {
                alert("Legal corporate name is required for the Business Associate Agreement signature");
                return;
            }
            if (!otpVerified || !otpProof) {
                setOtpStatus("You must send and verify your one-time password before continuing.", "err");
                return;
            }

            $("#result").html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> Registering...</div>');
            ensureActiveSession();

            $.ajax({
                url: 'register_process.php?site=<?php echo attr_js($siteId); ?>',
                type: 'POST',
                data: {
                    csrf_token_form: $('input[name="csrf_token_form"]').val(),
                    email: email,
                    password: password,
                    callback_url: callbackUrl,
                    TERMS_yes: '1',
                    BusAgree_yes: '1',
                    comms_consent: '0',
                    otp_channel: otpChannel,
                    otp_sms_destination: otpSmsDestination,
                    otp_proof: otpProof,
                    terms_signature_name: termsSignatureName,
                    terms_signer_title: termsSignerTitle,
                    terms_signed_at: termsSignedAt,
                    terms_practice_name: termsPracticeName,
                    terms_legal_corporate_name: termsLegalCorporateName,
                    baa_signature_name: baaSignatureName,
                    baa_signer_title: baaSignerTitle,
                    baa_signed_at: baaSignedAt,
                    baa_practice_name: baaPracticeName,
                    baa_legal_corporate_name: baaLegalCorporateName,
                    terms_version: termsAgreementVersion,
                    baa_version: baaAgreementVersion
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        sessionStorage.removeItem(onboardingDraftStorageKey);
                        sessionStorage.removeItem('medex_onboarding_summary');
                        location.href = 'onboarding.php?step=2&site=<?php echo attr_js($siteId); ?>';
                    } else if (response.refresh_required) {
                        const safeError = $('<div>').text(response.error || 'Agreement versions changed. Refresh and review the current agreements.').html();
                        $("#result").html(
                            '<div class="alert alert-danger">' +
                                safeError +
                                '<div style="margin-top:12px;">' +
                                    '<button type="button" class="btn btn-primary" id="medex-refresh-onboarding-btn" style="display:inline-flex;align-items:center;gap:8px;">' +
                                        '<i class="fa fa-refresh"></i> Refresh Agreements' +
                                    '</button>' +
                                '</div>' +
                            '</div>'
                        );
                        $("#medex-refresh-onboarding-btn").on("click", function() {
                            window.location.reload();
                        });
                    } else if (response.existing_account && response.reconnect_url) {
                        $("#result").html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> <?php echo xlj("Opening MedEx Admin Dashboard"); ?>...</div>');
                        window.location.href = response.reconnect_url;
                        return;
                    } else {
                        $("#result").html('<div class="alert alert-danger">' + response.error + '</div>');
                    }
                },
                error: function(jqXHR) {
                    $("#result").html('<div class="alert alert-danger">' + ajaxErrorMessage(jqXHR, 'Registration request failed') + '</div>');
                }
            });
        }

        function submitStep2() {
            const invalidCard = selectedServiceCards().find(function(card) {
                return !validateServiceSelections(card.serviceKey, true);
            });
            if (invalidCard) {
                return;
            }

            const draft = buildOnboardingDraftFromForm();
            if (!draft.items.length) {
                $("#result").html('<div class="alert alert-danger"><?php echo xlj("Select at least one service to continue."); ?></div>');
                return;
            }

            saveOnboardingDraft(draft);
            location.href = 'onboarding.php?step=3&site=<?php echo attr_js($siteId); ?>';
        }

        function buildOnboardingDraftFromForm() {
            const items = [];
            let total = 0;

            selectedServiceCards().forEach(function(card) {
                const serviceId = card.serviceKey;
                const definition = onboardingServiceDefinitions[serviceId] || {};
                const providerIds = card.requiresProviders ? getServiceSelectionValues(serviceId, 'providers') : [];
                const facilityIds = card.requiresFacilities ? getServiceSelectionValues(serviceId, 'facilities') : [];
                const providerBased = !!definition.provider_based;
                const quantity = providerBased ? providerIds.length : 1;
                const price = parseFloat(definition.price || 0);
                const effectiveQuantity = providerBased ? Math.max(quantity, 1) : 1;
                const lineTotal = providerBased ? (price * effectiveQuantity) : price;
                total += lineTotal;
                items.push({
                    serviceId: serviceId,
                    service: serviceId,
                    label: String(definition.title || onboardingServiceLabels[serviceId] || serviceId),
                    quantity: effectiveQuantity,
                    providerIds: providerIds,
                    facilityIds: facilityIds,
                    providerCount: providerIds.length,
                    facilityCount: facilityIds.length,
                    providerBased: providerBased,
                    price: price,
                    lineTotal: lineTotal
                });
            });

            return {
                items: items,
                total: Number(total.toFixed(2))
            };
        }

        function saveOnboardingDraft(draft) {
            const serialized = JSON.stringify(draft || {});
            sessionStorage.setItem(onboardingDraftStorageKey, serialized);
            sessionStorage.setItem('medex_onboarding_summary', serialized);
        }

        function loadOnboardingDraft() {
            const raw = sessionStorage.getItem(onboardingDraftStorageKey) || sessionStorage.getItem('medex_onboarding_summary');
            if (!raw) {
                return null;
            }
            try {
                const parsed = JSON.parse(raw);
                if (parsed && Array.isArray(parsed.items)) {
                    return parsed;
                }
            } catch (e) {
            }
            return null;
        }

        function serverCartToDraft() {
            const items = Array.isArray(onboardingServerCart.items) ? onboardingServerCart.items : [];
            if (!items.length) {
                return null;
            }
            const draftItems = items.map(function(item) {
                const serviceId = String(item && item.service ? item.service : '');
                if (!serviceId) {
                    return null;
                }
                const definition = onboardingServiceDefinitions[serviceId] || {};
                const quantity = Math.max(parseInt(item.quantity || 1, 10) || 1, 1);
                const price = parseFloat(definition.price || 0);
                return {
                    serviceId: serviceId,
                    service: serviceId,
                    label: String(definition.title || onboardingServiceLabels[serviceId] || serviceId),
                    quantity: quantity,
                    providerIds: Array.isArray(item.providers) ? item.providers.map(v => parseInt(v, 10)).filter(Number.isFinite) : [],
                    facilityIds: Array.isArray(item.facilities) ? item.facilities.map(v => parseInt(v, 10)).filter(Number.isFinite) : [],
                    providerCount: Array.isArray(item.providers) ? item.providers.length : quantity,
                    facilityCount: Array.isArray(item.facilities) ? item.facilities.length : 0,
                    providerBased: !!definition.provider_based,
                    price: price,
                    lineTotal: definition.provider_based ? (price * quantity) : price
                };
            }).filter(Boolean);
            if (!draftItems.length) {
                return null;
            }
            const total = onboardingServerCart.total !== null && onboardingServerCart.total !== undefined
                ? parseFloat(onboardingServerCart.total || 0)
                : draftItems.reduce(function(sum, item) { return sum + parseFloat(item.lineTotal || 0); }, 0);
            return {
                items: draftItems,
                total: Number((Number.isFinite(total) ? total : 0).toFixed(2))
            };
        }

        function getActiveOnboardingDraft() {
            const stored = loadOnboardingDraft();
            if (stored && stored.items.length) {
                return stored;
            }
            const serverDraft = serverCartToDraft();
            if (serverDraft && serverDraft.items.length) {
                saveOnboardingDraft(serverDraft);
                return serverDraft;
            }
            return { items: [], total: 0 };
        }

        function restoreOnboardingDraftToStep2() {
            const draft = getActiveOnboardingDraft();
            if (!draft.items.length) {
                return;
            }
            draft.items.forEach(function(item) {
                $("#form-step-2 input[name='selected_services[]'][value='" + item.serviceId.replace(/'/g, "\\'") + "']").prop('checked', true);
                (item.providerIds || []).forEach(function(providerId) {
                    $(configSelector(item.serviceId, 'providers') + "[value='" + providerId + "']").prop('checked', true);
                });
                (item.facilityIds || []).forEach(function(facilityId) {
                    $(configSelector(item.serviceId, 'facilities') + "[value='" + facilityId + "']").prop('checked', true);
                });
            });
        }

        function renderOnboardingReview(draft) {
            const items = Array.isArray(draft.items) ? draft.items : [];
            if (!items.length) {
                $("#summary-list").html('<li><?php echo xlj("No services selected"); ?></li>');
                $("#cart-total").text('$0.00');
                return;
            }
            const html = items.map(function(item) {
                const label = String(item.label || onboardingServiceLabels[item.serviceId] || item.serviceId);
                const parts = [];
                if (item.providerCount) {
                    parts.push(item.providerCount + ' <?php echo xlj("provider(s)"); ?>');
                }
                if (item.facilityCount) {
                    parts.push(item.facilityCount + ' <?php echo xlj("facility(ies)"); ?>');
                }
                const meta = parts.length ? ' <span style="color:#64748b;">(' + parts.join(' • ') + ')</span>' : '';
                return '<li>' + $('<div>').text(label).html() + meta + '</li>';
            }).join('');
            $("#summary-list").html(html);
            $("#cart-total").text('$' + parseFloat(draft.total || 0).toFixed(2));
        }

        function buildPendingChangesFromDraft(draft) {
            const items = Array.isArray(draft.items) ? draft.items : [];
            return {
                add: items.map(function(item) {
                    return {
                        serviceId: item.serviceId,
                        service: item.serviceId,
                        quantity: Math.max(parseInt(item.quantity || 1, 10) || 1, 1),
                        providerIds: Array.isArray(item.providerIds) ? item.providerIds : [],
                        facilityIds: Array.isArray(item.facilityIds) ? item.facilityIds : []
                    };
                }),
                remove: []
            };
        }

        function showOnboardingPaymentError(message) {
            const safeMessage = String(message || '').trim();
            if (!safeMessage) {
                $("#payment-errors").hide().text('');
                return;
            }
            $("#payment-errors").text(safeMessage).show();
        }

        function setOnboardingActionState(selector, label, disabled, showSpinner) {
            const $button = $(selector);
            if (!$button.length) {
                return;
            }
            const safeLabel = $('<div>').text(label).html();
            const prefix = showSpinner ? '<i class="fa fa-spinner fa-spin"></i> ' : '';
            $button.prop('disabled', !!disabled).html(prefix + safeLabel);
        }

        function submitOnboardingChanges(options = {}) {
            const draft = getActiveOnboardingDraft();
            if (!draft.items.length) {
                showOnboardingPaymentError('<?php echo xlj("No services selected. Return to Configure Services."); ?>');
                return;
            }
            window.pendingChanges = buildPendingChangesFromDraft(draft);
            showOnboardingPaymentError('');
            setOnboardingActionState('#review-changes-btn', '<?php echo xlj("Activate Services"); ?>', true, true);
            setOnboardingActionState('#payment-submit-btn', '<?php echo xlj("Complete Payment & Activate"); ?>', true, true);

            const requestData = {
                add: window.pendingChanges.add,
                remove: [],
                use_existing_payment: !!options.useExistingPayment,
                providers: {},
                facilities: {}
            };
            if (options.paymentNonce) {
                requestData.payment_nonce = options.paymentNonce;
            }
            window.pendingChanges.add.forEach(function(item) {
                if (Array.isArray(item.providerIds) && item.providerIds.length) {
                    requestData.providers[item.serviceId] = item.providerIds;
                }
                if (Array.isArray(item.facilityIds) && item.facilityIds.length) {
                    requestData.facilities[item.serviceId] = item.facilityIds;
                }
            });

            ensureActiveSession();
            fetch('<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/admin/process_subscription.php?site=<?php echo urlencode($siteId); ?>', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            })
            .then(function(response) {
                if (!response.ok) {
                    return response.text().then(function(text) {
                        throw new Error(text || ('HTTP ' + response.status));
                    });
                }
                return response.json();
            })
            .then(function(response) {
                if (!response || !response.success) {
                    throw new Error((response && response.error) || 'Activation failed.');
                }
                sessionStorage.removeItem(onboardingDraftStorageKey);
                sessionStorage.removeItem('medex_onboarding_summary');
                window.location.href = 'index.php?site=<?php echo attr_js($siteId); ?>&payment_success=1';
            })
            .catch(function(error) {
                showOnboardingPaymentError(error.message || 'Activation failed.');
                setOnboardingActionState('#review-changes-btn', '<?php echo xlj("Activate Services"); ?>', false, false);
                setOnboardingActionState('#payment-submit-btn', '<?php echo xlj("Complete Payment & Activate"); ?>', false, false);
            });
        }

        window._medexLoadScript = function(src) {
            return new Promise(function(resolve, reject) {
                const existing = Array.from(document.scripts).find(function(scriptTag) {
                    return scriptTag.src === src;
                });
                if (existing) {
                    if (existing.dataset.loaded === '1') {
                        resolve();
                        return;
                    }
                    existing.addEventListener('load', function() { resolve(); }, { once: true });
                    existing.addEventListener('error', function() { reject(new Error('Failed to load ' + src)); }, { once: true });
                    return;
                }
                const scriptTag = document.createElement('script');
                scriptTag.src = src;
                scriptTag.async = true;
                scriptTag.onload = function() {
                    scriptTag.dataset.loaded = '1';
                    resolve();
                };
                scriptTag.onerror = function() {
                    reject(new Error('Failed to load ' + src));
                };
                document.head.appendChild(scriptTag);
            });
        };

        function showOnboardingPaymentSection() {
            const paymentSection = document.getElementById('payment-section');
            if (paymentSection) {
                paymentSection.style.display = 'block';
                paymentSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function initialiseOnboardingPaymentForm(clientToken) {
            const payment = window._medexPayment;
            if (!clientToken) {
                showOnboardingPaymentError('Payment token missing.');
                return;
            }
            if (payment.ready && payment.token === clientToken) {
                showOnboardingPaymentSection();
                return;
            }
            payment.ready = false;
            payment.token = clientToken;
            showOnboardingPaymentError('');

            Promise.all([
                window._medexLoadScript('https://js.braintreegateway.com/web/3.97.2/js/client.min.js'),
                window._medexLoadScript('https://js.braintreegateway.com/web/3.97.2/js/hosted-fields.min.js')
            ]).then(function() {
                braintree.client.create({ authorization: clientToken }, function(clientErr, clientInstance) {
                    if (clientErr) {
                        showOnboardingPaymentError('Payment setup failed: ' + clientErr.message);
                        return;
                    }
                    payment.clientInstance = clientInstance;
                    braintree.hostedFields.create({
                        client: clientInstance,
                        styles: {
                            input: { 'font-size': '14px', color: '#1f2937' },
                            ':focus': { color: '#111827' }
                        },
                        fields: {
                            number: { selector: '#medex-card-number', placeholder: '4111 1111 1111 1111' },
                            expirationDate: { selector: '#medex-card-expiration', placeholder: 'MM/YY' },
                            cvv: { selector: '#medex-card-cvv', placeholder: '123' },
                            postalCode: { selector: '#medex-card-postal', placeholder: 'ZIP / Postal' }
                        }
                    }, function(fieldsErr, hostedFieldsInstance) {
                        if (fieldsErr) {
                            showOnboardingPaymentError('Card form setup failed: ' + fieldsErr.message);
                            return;
                        }
                        payment.hostedFieldsInstance = hostedFieldsInstance;
                        payment.ready = true;
                        showOnboardingPaymentSection();
                        setOnboardingActionState('#payment-submit-btn', '<?php echo xlj("Complete Payment & Activate"); ?>', false, false);
                    });
                });
            }).catch(function(error) {
                showOnboardingPaymentError(error.message || 'Payment libraries failed to load.');
            });
        }

        function requestOnboardingPaymentForm() {
            showOnboardingPaymentError('');
            setOnboardingActionState('#review-changes-btn', '<?php echo xlj("Activate Services"); ?>', true, true);
            ensureActiveSession();
            fetch('<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/admin/get_braintree_token.php?site=<?php echo urlencode($siteId); ?>', {
                credentials: 'include'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(response) {
                const clientToken = response && (response.clientToken || response.token);
                if (!response || !response.success || !clientToken) {
                    throw new Error((response && (response.error || response.message)) || 'Failed to initialize payment form.');
                }
                initialiseOnboardingPaymentForm(clientToken);
            })
            .catch(function(error) {
                showOnboardingPaymentError(error.message || 'Failed to initialize payment form.');
            })
            .finally(function() {
                setOnboardingActionState('#review-changes-btn', '<?php echo xlj("Activate Services"); ?>', false, false);
            });
        }

        function processOnboardingActivation() {
            const draft = getActiveOnboardingDraft();
            const total = parseFloat(draft.total || 0);
            const hasPaymentOnFile = window.braintreeToken !== null && window.braintreeToken !== undefined && window.braintreeToken !== '';

            if (!draft.items.length) {
                showOnboardingPaymentError('<?php echo xlj("No services selected. Return to Configure Services."); ?>');
                return;
            }
            if (!Number.isFinite(total) || total <= 0) {
                submitOnboardingChanges({ useExistingPayment: false });
                return;
            }
            if (hasPaymentOnFile) {
                submitOnboardingChanges({ useExistingPayment: true });
                return;
            }
            requestOnboardingPaymentForm();
        }

        function completeOnboardingPayment() {
            const payment = window._medexPayment;
            if (!payment || !payment.ready || !payment.hostedFieldsInstance) {
                showOnboardingPaymentError('Payment form not ready.');
                return;
            }
            const cardholderName = String($("#medex-cardholder-name").val() || '').trim();
            if (!cardholderName) {
                showOnboardingPaymentError('Please enter cardholder name.');
                return;
            }
            setOnboardingActionState('#payment-submit-btn', '<?php echo xlj("Complete Payment & Activate"); ?>', true, true);
            showOnboardingPaymentError('');
            payment.hostedFieldsInstance.tokenize({
                cardholderName: cardholderName
            }, function(error, payload) {
                if (error) {
                    showOnboardingPaymentError(error.message || 'Unable to tokenize payment details.');
                    setOnboardingActionState('#payment-submit-btn', '<?php echo xlj("Complete Payment & Activate"); ?>', false, false);
                    return;
                }
                submitOnboardingChanges({
                    paymentNonce: payload && payload.nonce ? payload.nonce : '',
                    useExistingPayment: false
                });
            });
        }

        $(document).ready(function() {
            const agreementModal = $("#agreement-modal");
            const agreementFrame = $("#agreement-frame");
            const agreementTitle = $("#agreement-modal-title");
            const agreementSignUrlBase = <?php echo json_encode($agreementSignUrlBase); ?>;
            const termsVersion = <?php echo json_encode($termsVersion); ?>;
            const baaVersion = <?php echo json_encode($baaVersion); ?>;
            const termsUrl = <?php echo json_encode($termsUrl); ?>;
            const baaUrl = <?php echo json_encode($baaUrl); ?>;
            let activeAgreementType = null;

            function closeAgreementModal() {
                const agreementType = activeAgreementType;
                agreementModal.removeClass("show").attr("aria-hidden", "true");
                agreementFrame.attr("src", "about:blank");
                activeAgreementType = null;
                if (agreementType === "terms" || agreementType === "baa") {
                    restoreSavedAgreementState(agreementType);
                }
            }
            window.medexCloseAgreementModal = closeAgreementModal;

            function markAgreementAccepted(type) {
                if (type === "terms") {
                    $("#terms_completed").val("1");
                    $("#TERMS_yes").prop("checked", true);
                } else if (type === "baa") {
                    $("#baa_completed").val("1");
                    $("#BusAgree_yes").prop("checked", true);
                }
                syncAgreementCheckboxState();
                updateStep1SubmitState();
            }

            function agreementReceiptBaseUrl(type, options = {}) {
                const version = (type === "terms") ? termsVersion : baaVersion;
                const params = new URLSearchParams({
                    site: <?php echo json_encode($siteId); ?>,
                    type: type,
                    version: version
                });
                if (options.edit) {
                    params.set("edit", "1");
                }
                if (options.artifact) {
                    params.set("artifact", "1");
                }
                if (options.autodownload) {
                    params.set("autodownload", "1");
                }
                if (options.autoprint) {
                    params.set("autoprint", "1");
                }
                return "agreement_sign.php?" + params.toString();
            }

            function updateAgreementReceiptActions(type, signed) {
                const selector = (type === "terms") ? "#terms-receipt-actions" : "#baa-receipt-actions";
                $(selector).toggle(!!signed);
            }

            function applyAgreementSignature(type, payload) {
                const signerName = String(payload.signer_name || "").trim();
                const signerTitle = String(payload.signer_title || "").trim();
                const signedAt = String(payload.signed_at || "").trim();
                const agreementVersion = String(payload.agreement_version || "").trim();
                if (!signerName || !signedAt) {
                    return;
                }
                if (type === "terms") {
                    $("#terms_signature_name").val(signerName);
                    $("#terms_signer_title").val(signerTitle);
                    $("#terms_signed_at").val(signedAt);
                    $("#terms_agreement_version").val(agreementVersion || termsVersion);
                    $("#terms_practice_name").val(String(payload.practice_name || "").trim());
                    $("#terms_legal_corporate_name").val(String(payload.legal_corporate_name || "").trim());
                } else if (type === "baa") {
                    $("#baa_signature_name").val(signerName);
                    $("#baa_signer_title").val(signerTitle);
                    $("#baa_signed_at").val(signedAt);
                    $("#baa_agreement_version").val(agreementVersion || baaVersion);
                    $("#baa_practice_name").val(String(payload.practice_name || "").trim());
                    $("#baa_legal_corporate_name").val(String(payload.legal_corporate_name || "").trim());
                }
            }

            function restoreSavedAgreementState(type) {
                const version = (type === "terms") ? termsVersion : baaVersion;
                $.ajax({
                    url: agreementSignUrlBase + "&type=" + encodeURIComponent(type) + "&version=" + encodeURIComponent(version) + "&action=status",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                    if (!response || !response.success || !response.signed || !response.payload) {
                        if (response && response.expired) {
                            if (type === "terms") {
                                $("#terms_completed").val("0");
                                $("#TERMS_yes").prop("checked", false);
                            } else if (type === "baa") {
                                $("#baa_completed").val("0");
                                $("#BusAgree_yes").prop("checked", false);
                            }
                            syncAgreementCheckboxState();
                            updateStep1SubmitState();
                        }
                        return;
                    }
                        response.payload.agreement_version = String(response.agreement_version || response.payload.agreement_version || "");
                        applyAgreementSignature(type, response.payload);
                        markAgreementAccepted(type);
                        updateAgreementReceiptActions(type, true);
                    }
                });
            }

            function openAgreementModal(type, displayUrl) {
                activeAgreementType = type;
                const version = (type === "terms") ? termsVersion : baaVersion;
                const signUrl = agreementSignUrlBase + "&type=" + encodeURIComponent(type) + "&version=" + encodeURIComponent(version);
                if (type === "terms") {
                    agreementTitle.text("MedEx Terms and Conditions");
                } else {
                    agreementTitle.text("MedEx Business Associate Agreement (BAA)");
                }
                agreementFrame.attr("src", signUrl);
                agreementModal.addClass("show").attr("aria-hidden", "false");
            }

            function openAgreementView(type) {
                openAgreementModal(type);
            }

            function openAgreementEdit(type) {
                activeAgreementType = type;
                const signUrl = agreementReceiptBaseUrl(type, { edit: true });
                if (type === "terms") {
                    agreementTitle.text("MedEx Terms and Conditions");
                } else {
                    agreementTitle.text("MedEx Business Associate Agreement (BAA)");
                }
                agreementFrame.attr("src", signUrl);
                agreementModal.addClass("show").attr("aria-hidden", "false");
            }

            function openAgreementDownload(type) {
                window.open(agreementReceiptBaseUrl(type, { artifact: true, autodownload: true }), "_blank", "noopener,noreferrer");
            }

            $("#open-terms-link").on("click", function(e) {
                e.preventDefault();
                openAgreementModal("terms", termsUrl);
            });
            $("#open-baa-link").on("click", function(e) {
                e.preventDefault();
                openAgreementModal("baa", baaUrl);
            });
            $("#agreement-close-btn").on("click", function() {
                closeAgreementModal();
            });
            $("#terms-view-btn").on("click", function() {
                openAgreementView("terms");
            });
            $("#terms-download-btn").on("click", function() {
                openAgreementDownload("terms");
            });
            $("#terms-edit-btn").on("click", function() {
                openAgreementEdit("terms");
            });
            $("#baa-view-btn").on("click", function() {
                openAgreementView("baa");
            });
            $("#baa-download-btn").on("click", function() {
                openAgreementDownload("baa");
            });
            $("#baa-edit-btn").on("click", function() {
                openAgreementEdit("baa");
            });
            agreementModal.on("click", function(e) {
                if (e.target === this) {
                    closeAgreementModal();
                }
            });
            window.addEventListener("message", function(event) {
                if (!event || !event.data || typeof event.data !== "object") {
                    return;
                }
                if (event.data.source !== "medex-agreement-signer") {
                    return;
                }
                const type = String(event.data.type || "");
                if ((type !== "terms" && type !== "baa") || type !== activeAgreementType) {
                    return;
                }
                if (event.data.action === "signed") {
                    applyAgreementSignature(type, event.data);
                    markAgreementAccepted(type);
                    updateAgreementReceiptActions(type, true);
                    return;
                }
                if (event.data.action === "close") {
                    closeAgreementModal();
                }
            });
            $(document).on("keydown", function(e) {
                if (e.key === "Escape" && agreementModal.hasClass("show")) {
                    closeAgreementModal();
                }
            });

            if (wizardStep === 3) {
                $("#wizard-progress-fill").css("width", "100%");
                const draft = getActiveOnboardingDraft();
                renderOnboardingReview(draft);
                $("#edit-services-btn").on("click", function() {
                    location.href = 'onboarding.php?step=2&site=<?php echo attr_js($siteId); ?>';
                });
                $("#review-changes-btn").on("click", function() {
                    processOnboardingActivation();
                });
                $("#payment-submit-btn").on("click", function() {
                    completeOnboardingPayment();
                });
            } else if (wizardStep === 2) {
                restoreOnboardingDraftToStep2();
                syncServiceConfigPanels();
                updateStep2Progress();
                $("#form-step-2 input[type='checkbox']").on("change", function() {
                    syncServiceConfigPanels();
                    updateStep2Progress();
                    updateBillingSummary();
                });
                $(document).on("change", "input[name^='service_config[']", function() {
                    updateBillingSummary();
                    updateStep2Progress();
                });
                $(document).on("click", "[data-select-group][data-select-action]", function() {
                    const serviceKey = String($(this).attr("data-service-key") || '');
                    const group = $(this).attr("data-select-group");
                    const action = $(this).attr("data-select-action");
                    const selector = configSelector(serviceKey, group);
                    $(selector).prop("checked", action === "all");
                    updateBillingSummary();
                    updateStep2Progress();
                });
                $("#form-step-2 .service-card").on("click", function(e) {
                    const $target = $(e.target);
                    if ($target.closest("input, a, button, select, textarea, label, .provider-list, .service-config-panel").length) {
                        return;
                    }
                    const $serviceToggle = $(this).children("input[type='checkbox'][name='selected_services[]']").first();
                    if ($serviceToggle.length) {
                        $serviceToggle.prop("checked", !$serviceToggle.prop("checked")).trigger("change");
                    }
                });
                updateBillingSummary();
            }

            $("#toggle-password").on("click", function() {
                togglePasswordField("#password", "#toggle-password");
            });
            $("#toggle-rpassword").on("click", function() {
                togglePasswordField("#rpassword", "#toggle-rpassword");
            });
            $("#otp_channel").on("change", function() {
                updateOtpDestinationVisibility();
            });
            $("#send-otp-btn").on("click", function() {
                sendOtp();
            });
            $("#verify-otp-btn").on("click", function() {
                verifyOtp();
            });
            $("#email").on("blur", function() {
                validateEmailField(true);
                restoreOtpStatusForCurrentIdentity();
                updateStep1SubmitState();
            });
            $("#email").on("input", function() {
                if (validateEmailField(false)) {
                    clearFieldError("#email", "#email-error");
                }
                resetLocalOtpVerification();
                updateStep1SubmitState();
            });
            $("#password").on("blur", function() {
                validatePasswordField(true);
                validateConfirmPasswordField(true);
                updateStep1SubmitState();
            });
            $("#password").on("input", function() {
                if (validatePasswordField(false)) {
                    clearFieldError("#password", "#password-error");
                }
                validateConfirmPasswordField(false);
                updateStep1SubmitState();
            });
            $("#rpassword").on("input blur", function() {
                validateConfirmPasswordField(true);
                updateStep1SubmitState();
            });
            $("#otp_sms_destination").on("blur", function() {
                if ($("#otp_channel").val() === "sms") {
                    validateSmsField(true);
                }
                restoreOtpStatusForCurrentIdentity();
            });
            $("#otp_sms_destination").on("input", function() {
                clearFieldError("#otp_sms_destination", "#otp-sms-error");
                resetLocalOtpVerification();
            });
            $("#TERMS_yes, #BusAgree_yes").on("change", function() {
                updateStep1SubmitState();
            });
            updateOtpDestinationVisibility();
            syncAgreementCheckboxState();
            restoreOtpStatusForCurrentIdentity();
            restoreSavedAgreementState("terms");
            restoreSavedAgreementState("baa");
            updateOtpUiState();
            updateStep1SubmitState();
        });
    </script>
</body>
</html>
