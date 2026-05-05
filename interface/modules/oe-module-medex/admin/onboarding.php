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
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Modules\MedEx\MedExConfig;

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo "Access denied";
    exit;
}

// Load MedEx API
require_once(__DIR__ . '/../src/MedExAPI.php');
$api = new \OpenEMR\Modules\MedEx\MedExAPI();
$step = $_GET['step'] ?? '1';
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
if ($session) {
    $csrfToken = (string) CsrfUtils::collectCsrfToken(session: $session);
} else {
    $csrfToken = (string) CsrfUtils::collectCsrfToken();
}
$termsVersion = MedExConfig::TERMS_VERSION;
$baaVersion = MedExConfig::BAA_VERSION;
$termsUrl = MedExConfig::termsUrl();
$baaUrl = MedExConfig::baaUrl();
$privacyUrl = MedExConfig::privacyUrl();
$siteId = (string)($_GET['site'] ?? 'default');
$agreementSignUrlBase = 'agreement_sign.php?site=' . urlencode($siteId);
$sessionCartItems = (isset($_SESSION['medex_cart_items']) && is_array($_SESSION['medex_cart_items'])) ? $_SESSION['medex_cart_items'] : [];
$sessionCartTotal = isset($_SESSION['medex_cart_total']) ? (float)$_SESSION['medex_cart_total'] : null;
$defaultOpenEmrUrl = '';
if (!empty($_SERVER['HTTP_HOST'])) {
    $webroot = trim((string)($GLOBALS['webroot'] ?? ''), '/');
    $defaultOpenEmrUrl = 'https://' . $_SERVER['HTTP_HOST'] . ($webroot !== '' ? '/' . $webroot : '');
}

// Fetch pricing from API for step 2
$pricing = $api->getPricing();
error_log('[ONBOARDING DEBUG] Pricing data: ' . print_r($pricing, true));
$pricingServices = is_array($pricing['services'] ?? null) ? $pricing['services'] : [];
$normalizeServiceId = static function (string $rawId, array $svc): string {
    $id = strtolower(trim($rawId));
    $name = strtolower(trim((string)($svc['name'] ?? '')));
    if ($id === 'calendar service' || $id === 'calendar_service' || $name === 'calendar services') {
        return 'calendar_ai';
    }
    if ($id === 'calendar export' || $id === 'calendar_export') {
        return 'calendar_export';
    }
    return preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '_', $id));
};
$serviceUiOverrides = [
    'appointment_reminders' => [
        'title' => xlt('Reminders & Recalls'),
        'description' => xlt('Automated appointment reminders (SMS/Email/Voice) and comprehensive Recall Board management.'),
        'help_topic' => 'reminders',
        'sort' => 10,
        'default_checked' => true,
    ],
    'calendar_export' => [
        'title' => xlt('Calendar Export'),
        'description' => xlt('Read-only web calendar with export capabilities for external scheduling systems.'),
        'help_topic' => 'calendar_export',
        'sort' => 20,
    ],
    'calendar_ai' => [
        'title' => xlt('Calendar Services'),
        'description' => xlt('Template Builder, Patient Rescheduling Bot, and Auto Cancellation List services.'),
        'help_topic' => 'calendar_services',
        'sort' => 30,
    ],
    'secure_chat' => [
        'title' => xlt('Secure Chat (Practice-wide)'),
        'description' => xlt('HIPAA-compliant two-way messaging for all staff and patients.'),
        'help_topic' => 'secure_chat',
        'sort' => 40,
    ],
    'pdf_management' => [
        'title' => xlt('PDF Form Management'),
        'description' => xlt('Digital form filling, signature capture, and AI data extraction.'),
        'help_topic' => 'pdf_management',
        'sort' => 50,
    ],
];
$serviceCatalog = [];
foreach ($pricingServices as $rawServiceId => $svc) {
    $rawServiceId = (string)$rawServiceId;
    if ($rawServiceId === '') {
        continue;
    }
    $svc = is_array($svc) ? $svc : [];
    $serviceId = $normalizeServiceId($rawServiceId, $svc);
    if ($serviceId === '') {
        continue;
    }
    $override = $serviceUiOverrides[$serviceId] ?? [];
    $fallbackTitle = ucwords(str_replace('_', ' ', $serviceId));
    $title = (string)($override['title'] ?? ($svc['display_name'] ?? ($svc['name'] ?? $fallbackTitle)));
    $description = (string)($override['description'] ?? ($svc['description'] ?? ''));
    $helpTopic = (string)($override['help_topic'] ?? ('service_' . $serviceId));
    $sort = (int)($override['sort'] ?? 500);
    $providerBased = !empty($svc['provider_based']);
    $serviceCatalog[] = [
        'id' => $serviceId,
        'title' => $title,
        'description' => $description,
        'help_topic' => $helpTopic,
        'sort' => $sort,
        'provider_based' => $providerBased,
        'default_checked' => !empty($override['default_checked']),
        'price' => (float)($svc['price'] ?? 0),
        'unit' => (string)($svc['unit'] ?? ''),
        'trial' => is_array($svc['trial'] ?? null) ? $svc['trial'] : null,
    ];
}
usort($serviceCatalog, function (array $a, array $b): int {
    if ($a['sort'] === $b['sort']) {
        return strcasecmp((string)$a['title'], (string)$b['title']);
    }
    return $a['sort'] <=> $b['sort'];
});
$onboardingAllow = ['calendar_full', 'calendar_ai'];
$serviceCatalog = array_values(array_filter($serviceCatalog, static function (array $svc) use ($onboardingAllow): bool {
    return in_array((string)$svc['id'], $onboardingAllow, true);
}));
$serviceLabelMap = [];
$hasProviderBasedService = false;
foreach ($serviceCatalog as $svc) {
    $serviceLabelMap[$svc['id']] = $svc['title'];
    if (!empty($svc['provider_based'])) {
        $hasProviderBasedService = true;
    }
}

// If already configured and active, redirect to settings
if ($api->isConfigured() && $api->isActive()) {
    $services = $api->getEnabledServices();
    if (!empty($services)) {
        header('Location: settings.php');
        exit;
    }
}

// Stage 2 & 3 require registration first
if ($step > 1 && !$api->isConfigured()) {
    header('Location: onboarding.php?step=1');
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
        body { background: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .wizard-container { max-width: 1100px; margin: 50px auto; background: #f5f5f5; padding: 32px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .wizard-header { text-align: center; margin-bottom: 40px; position: relative; }
        .wizard-back-link { position: absolute; left: 0; top: 12px; color: #64748b; text-decoration: none; font-size: 14px; }
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
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            color: #111827 !important;
            background: #ffffff !important;
        }
        select.form-control,
        select.form-control option {
            color: #111827 !important;
            background: #ffffff !important;
        }
        .form-control.invalid { border-color: #dc2626; }
        .field-error { color: #dc2626; font-size: 12px; margin-top: 6px; display: none; }
        .form-control::placeholder { color: #9ca3af; opacity: 1; }
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

        .service-card { border: 2px solid #e0e0e0; padding: 20px; border-radius: 10px; margin-bottom: 15px; display: flex; align-items: flex-start; gap: 15px; transition: all 0.2s ease; background: #fff; }
        .service-card:hover { border-color: #667eea; background: #f8f9ff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transform: translateY(-1px); }
        .service-info { flex: 1; }
        .service-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
        .service-title { font-size: 17px; font-weight: 600; color: #333; margin-bottom: 6px; }
        .service-help-icon { color: #0f4b8f; text-decoration: none; font-size: 16px; line-height: 1; padding-top: 2px; }
        .service-help-icon:hover { color: #0a3460; }
        .service-desc { font-size: 13px; color: #666; line-height: 1.5; }
        .service-price { font-size: 14px; color: #0f4b8f; font-weight: 600; margin-top: 8px; }

        .provider-list { max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 6px; margin-top: 10px; }
        .provider-item { display: flex; align-items: center; gap: 10px; padding: 5px 0; border-bottom: 1px solid #f5f5f5; }
        .onboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 8px; }
        .panel-card { border: 2px solid #e0e0e0; border-radius: 10px; padding: 20px; background: #ffffff; transition: all 0.2s ease; }
        .panel-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-color: #d3d9e6; }
        .panel-card .form-group:last-child { margin-bottom: 0; }
        .full-width-card { margin-top: 14px; }

        #result { margin-top: 20px; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .otp-panel { border: 1px solid #d9e2ef; border-radius: 8px; padding: 14px; margin-top: 10px; background: #f8f9ff; }
        .otp-inline { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-top: 10px; }
        .otp-status { font-size: 13px; color: #475569; margin-top: 8px; }
        .otp-status.ok { color: #15803d; }
        .otp-status.err { color: #b91c1c; }
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
        .agreement-modal {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.58);
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .agreement-modal.show {
            display: flex;
        }
        .agreement-modal-panel {
            width: min(1100px, 98vw);
            height: min(850px, 96vh);
            background: #fff;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 24px 64px rgba(2, 6, 23, 0.45);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .agreement-modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            background: #0f4b8f;
            color: #fff;
            padding: 10px 14px;
            font-weight: 700;
        }
        .agreement-modal-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .agreement-frame {
            border: 0;
            width: 100%;
            height: 100%;
            flex: 1 1 auto;
            background: #fff;
        }
        @media (max-width: 900px) {
            .onboard-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="wizard-container">
        <div class="wizard-header">
            <a href="splash.php" class="wizard-back-link">
                <i class="fa fa-arrow-left"></i> <?php echo xlt("Back to Overview"); ?>
            </a>
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
                        <div class="form-group">
                            <label for="email"><?php echo xlt("Administrator E-mail"); ?></label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="admin@practice.com" required>
                            <small style="color:#64748b; display:block; margin-top:6px;"><?php echo xlt("This email will be your username for signing in to MedEx."); ?></small>
                            <div id="email-error" class="field-error"><?php echo xlt("Please enter a valid administrator email address."); ?></div>
                        </div>
                        <div class="form-group">
                            <label for="password"><?php echo xlt("Password"); ?></label>
                            <div class="password-wrap">
                                <input type="password" id="password" name="password" class="form-control" required style="padding-right:40px;">
                                <i class="fa fa-eye-slash password-toggle" id="toggle-password" title="<?php echo xla("Show/Hide Password"); ?>"></i>
                            </div>
                            <small style="color:#64748b; display:block; margin-top:6px;"><?php echo xlt("Use at least 8 characters with uppercase, lowercase, number, and special character."); ?></small>
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
                    <div class="service-title" style="margin-bottom: 6px;"><?php echo xlt("Send One-Time Passcode"); ?></div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <select id="otp_channel" name="otp_channel" class="form-control">
                            <option value="email"><?php echo xlt("Email One-Time Password (OTP)"); ?></option>
                            <option value="sms"><?php echo xlt("SMS One-Time Password (OTP)"); ?></option>
                        </select>
                        <small style="color:#64748b;">
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
                        <div class="otp-inline">
                            <button type="button" class="btn btn-primary" id="send-otp-btn"><?php echo xlt("Send One-Time Password (OTP)"); ?></button>
                        </div>
                        <div class="otp-inline">
                            <input type="text" id="otp_code" class="form-control" style="max-width: 220px;" placeholder="<?php echo xla("Enter 6-digit code"); ?>" maxlength="6">
                            <button type="button" class="btn" style="background:#e2e8f0;" id="verify-otp-btn"><?php echo xlt("Verify Code"); ?></button>
                        </div>
                        <div id="otp-status" class="otp-status"><?php echo xlt("Send and verify your one-time password before continuing."); ?></div>
                        <input type="hidden" id="otp_proof" name="otp_proof" value="">
                    </div>
                </div>

                <div class="panel-card full-width-card">
                    <div class="service-title" style="margin-bottom: 10px;"><?php echo xlt("Required Agreements"); ?></div>
                    <input type="hidden" id="terms_completed" name="terms_completed" value="0">
                    <input type="hidden" id="baa_completed" name="baa_completed" value="0">
                    <input type="hidden" id="terms_signature_name" name="terms_signature_name" value="">
                    <input type="hidden" id="terms_signer_title" name="terms_signer_title" value="">
                    <input type="hidden" id="terms_signed_at" name="terms_signed_at" value="">
                    <input type="hidden" id="terms_practice_name" name="terms_practice_name" value="">
                    <input type="hidden" id="terms_legal_corporate_name" name="terms_legal_corporate_name" value="">
                    <input type="hidden" id="baa_signature_name" name="baa_signature_name" value="">
                    <input type="hidden" id="baa_signer_title" name="baa_signer_title" value="">
                    <input type="hidden" id="baa_signed_at" name="baa_signed_at" value="">
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
                    </div>
                    <div class="agreement-note">
                        <?php echo xlt("Open each agreement link and complete electronic signature to unlock its checkbox."); ?>
                    </div>
                </div>
                <div style="margin-top: 30px; text-align: right;">
                    <button type="button" id="step1-next-btn" class="btn btn-primary" onclick="submitStep1()" disabled><?php echo xlt("Next: Configure Services"); ?> <i class="fa fa-arrow-right"></i></button>
                </div>
            </form>

            <div id="agreement-modal" class="agreement-modal" aria-hidden="true">
                <div class="agreement-modal-panel">
                    <div class="agreement-modal-head">
                        <div id="agreement-modal-title"><?php echo xlt("Agreement"); ?></div>
                        <div class="agreement-modal-actions">
                            <button type="button" id="agreement-close-btn" class="btn" style="background:#e2e8f0;padding:8px 12px;font-size:13px;">
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
                <?php $helpBaseUrl = 'help_center.php?site=' . urlencode((string)$siteId); ?>
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
                <?php if (empty($serviceCatalog)): ?>
                <div class="alert alert-danger"><?php echo xlt("No services are currently available for onboarding. Please try again shortly."); ?></div>
                <?php endif; ?>
                <?php foreach ($serviceCatalog as $service): ?>
                <?php
                $serviceId = (string)$service['id'];
                $checkboxId = 'service_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $serviceId);
                $helpHref = $helpBaseUrl . '&topic=' . urlencode((string)$service['help_topic']);
                $providerBased = !empty($service['provider_based']);
                $trial = is_array($service['trial'] ?? null) ? $service['trial'] : null;
                $fallbackUnit = $providerBased ? 'mo/per provider' : 'mo';
                ?>
                <div class="service-card">
                    <input
                        type="checkbox"
                        name="<?php echo attr($checkboxId); ?>"
                        id="<?php echo attr($checkboxId); ?>"
                        data-service-id="<?php echo attr($serviceId); ?>"
                        data-provider-based="<?php echo $providerBased ? '1' : '0'; ?>"
                        <?php echo !empty($service['default_checked']) ? 'checked' : ''; ?>
                    >
                    <div class="service-info">
                        <div class="service-header">
                            <div class="service-title"><?php echo text((string)$service['title']); ?></div>
                            <a class="service-help-icon" href="<?php echo attr($helpHref); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo xla('Service Help'); ?>" aria-label="<?php echo xla('Service Help'); ?>">
                                <i class="fa fa-question-circle" aria-hidden="true"></i>
                            </a>
                        </div>
                        <?php if (!empty($service['description'])): ?>
                        <div class="service-desc"><?php echo text((string)$service['description']); ?></div>
                        <?php endif; ?>
                        <div class="service-price">
                            <?php
                            if ($trial && !empty($trial['enabled'])) {
                                echo "<span style='color: #28a745; font-weight: 600;'>" . xlt("Trial:") . " ";
                                if ((float)($trial['price'] ?? 0) == 0.0) {
                                    echo (int)($trial['duration'] ?? 1) . " " . xlt((string)($trial['frequency'] ?? 'month')) . ((int)($trial['duration'] ?? 1) > 1 ? "s" : "") . " " . xlt("free");
                                } else {
                                    echo "$" . number_format((float)($trial['price'] ?? 0), 2) . " / " . xlt((string)($trial['frequency'] ?? 'month')) . " " . xlt("for") . " " . (int)($trial['duration'] ?? 1) . " " . xlt((string)($trial['frequency'] ?? 'month')) . ((int)($trial['duration'] ?? 1) > 1 ? "s" : "");
                                }
                                echo "</span><br>";
                                echo "<span style='font-size: 0.9em;'>" . xlt("Then") . " $" . number_format((float)($service['price'] ?? 0), 2) . " " . text($formatUnit((string)($service['unit'] ?? ''), $fallbackUnit)) . "</span>";
                            } else {
                                echo "$" . number_format((float)($service['price'] ?? 0), 2) . " " . text($formatUnit((string)($service['unit'] ?? ''), $fallbackUnit));
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (!empty($hasProviderBasedService)): ?>
                <div id="provider-selection-block" class="panel-card" style="margin-top: 12px;">
                    <label><?php echo xlt("Select Providers for Provider-Based Services"); ?>:</label>
                    <div class="provider-list">
                        <?php
                        $res = sqlStatement("SELECT id, fname, lname FROM users WHERE authorized=1 AND active=1 ORDER BY lname");
                        while ($row = sqlFetchArray($res)) {
                            echo "<div class='provider-item'><input type='checkbox' name='reminders_providers[]' value='{$row['id']}'> {$row['lname']}, {$row['fname']}</div>";
                        }
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <div style="margin-top: 30px; display: flex; justify-content: space-between;">
                    <button type="button" class="btn" style="background: #eee;" onclick="location.href='onboarding.php?step=1'"><?php echo xlt("Back"); ?></button>
                    <button type="button" class="btn btn-primary" onclick="submitStep2()"><?php echo xlt("Next: Activation & Payment"); ?> <i class="fa fa-arrow-right"></i></button>
                </div>
            </form>

        <?php elseif ($step == 3): ?>
            <!-- Step 3: Payment & Activation -->
            <div>
                <h3><?php echo xlt("Review & Payment"); ?></h3>
                <p style="color: #666;">
                    <?php echo xlt("Review your subscription and complete payment to activate your services."); ?>
                </p>

                <!-- Cart Summary -->
                <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <strong><?php echo xlt("Subscription Summary"); ?>:</strong>
                    <ul id="summary-list" style="margin-top: 10px;">
                        <!-- Populate via JS -->
                    </ul>
                    <div style="border-top: 2px solid #ddd; margin-top: 15px; padding-top: 15px;">
                        <strong><?php echo xlt("Total"); ?>:</strong> <span id="cart-total" style="font-size: 20px; color: #0f4b8f;">$0.00 / <?php echo xlt("month"); ?></span>
                    </div>
                </div>

                <!-- Braintree Payment Form -->
                <div style="margin: 30px 0;">
                    <h4><?php echo xlt("Payment Information"); ?></h4>
                    <form id="payment-form">
                        <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>" />
                        <div class="form-group">
                            <label for="cardholder-name"><?php echo xlt("Cardholder Name"); ?></label>
                            <input type="text" id="cardholder-name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label><?php echo xlt("Card Number"); ?></label>
                            <div id="card-number" style="height: 45px; padding: 10px; border: 1px solid #ddd; border-radius: 6px;"></div>
                        </div>
                        <div style="display: flex; gap: 15px;">
                            <div class="form-group" style="flex: 1;">
                                <label><?php echo xlt("Expiration Date"); ?></label>
                                <div id="expiration-date" style="height: 45px; padding: 10px; border: 1px solid #ddd; border-radius: 6px;"></div>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label><?php echo xlt("CVV"); ?></label>
                                <div id="cvv" style="height: 45px; padding: 10px; border: 1px solid #ddd; border-radius: 6px;"></div>
                            </div>
                        </div>
                        <div id="payment-errors" class="alert alert-danger" style="display: none; margin-top: 15px;"></div>
                        <div style="margin-top: 30px; display: flex; justify-content: space-between;">
                            <button type="button" class="btn" style="background: #eee;" onclick="location.href='onboarding.php?step=2'"><?php echo xlt("Back"); ?></button>
                            <button type="submit" class="btn btn-primary" id="submit-payment">
                                <i class="fa fa-lock"></i> <?php echo xlt("Complete Payment & Activate"); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div id="result"></div>
    </div>

    <script>
        let otpVerified = false;
        const wizardStep = <?php echo (int)$step; ?>;

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

        function updateOtpDestinationVisibility() {
            const channel = $("#otp_channel").val();
            if (channel === "sms") {
                $("#otp-sms-destination-wrap").show();
            } else {
                $("#otp-sms-destination-wrap").hide();
                $("#otp_sms_destination").val("");
                clearFieldError("#otp_sms_destination", "#otp-sms-error");
            }
            otpVerified = false;
            $("#otp_proof").val("");
            $("#otp_code").val("");
            setOtpStatus("Send and verify your one-time password before continuing.");
            updateOtpConsentCopy();
            updateStep1SubmitState();
        }

        function updateOtpConsentCopy() {
            const channel = $("#otp_channel").val() === "sms" ? "text message" : "email";
            $("#otp-consent-copy").text(
                "You agree to allow us to send you this " + channel + " to verify your identity before enabling your MedEx setup. "
            );
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
            return $("#terms_completed").val() === "1";
        }

        function isBaaCompleted() {
            return $("#baa_completed").val() === "1";
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
            const anyServiceSelected = $("#form-step-2 input[type='checkbox'][name^='service_']:checked").length > 0;
            const providerBasedSelected = $("#form-step-2 input[type='checkbox'][name^='service_'][data-provider-based='1']:checked").length > 0;
            const providerReady = !providerBasedSelected || $("input[name='reminders_providers[]']:checked").length > 0;
            const completed = (anyServiceSelected ? 1 : 0) + (providerReady ? 1 : 0);
            const pct = 50 + Math.round((completed / 2) * 50);
            $("#wizard-progress-fill").css("width", pct + "%");
        }

        function sendOtp(retried = false) {
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
                    } else if (!retried && response && response.error === "Invalid security token" && response.csrf_token) {
                        $('input[name="csrf_token_form"]').val(response.csrf_token);
                        sendOtp(true);
                    } else {
                        setOtpStatus(response.error || "Unable to send one-time password.", "err");
                    }
                },
                error: function() {
                    setOtpStatus(ajaxErrorMessage(arguments[0], "Unable to send one-time password due to a request error."), "err");
                }
            });
        }

        function verifyOtp(retried = false) {
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
                    } else if (!retried && response && response.error === "Invalid security token" && response.csrf_token) {
                        $('input[name="csrf_token_form"]').val(response.csrf_token);
                        verifyOtp(true);
                    } else {
                        otpVerified = false;
                        $("#otp_proof").val("");
                        setOtpStatus(response.error || "One-time password verification failed.", "err");
                    }
                    updateStep1SubmitState();
                },
                error: function() {
                    otpVerified = false;
                    $("#otp_proof").val("");
                    setOtpStatus(ajaxErrorMessage(arguments[0], "Unable to verify one-time password due to a request error."), "err");
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
            const termsPracticeName = ($("#terms_practice_name").val() || "").trim();
            const termsLegalCorporateName = ($("#terms_legal_corporate_name").val() || "").trim();
            const baaSignatureName = ($("#baa_signature_name").val() || "").trim();
            const baaSignerTitle = ($("#baa_signer_title").val() || "").trim();
            const baaSignedAt = ($("#baa_signed_at").val() || "").trim();
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
                url: 'register_process.php',
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
                    terms_version: '<?php echo attr_js($termsVersion); ?>',
                    baa_version: '<?php echo attr_js($baaVersion); ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.href = 'onboarding.php?step=2';
                    } else {
                        $("#result").html('<div class="alert alert-danger">' + response.error + '</div>');
                    }
                },
                error: function(jqXHR) {
                    $("#result").html('<div class="alert alert-danger">' + ajaxErrorMessage(jqXHR, 'Registration request failed') + '</div>');
                }
            });
        }

        function submitStep2(retried = false) {
            // Create cart with MedEx API
            const formData = $("#form-step-2").serialize();

            $("#result").html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> Creating cart...</div>');

            $.ajax({
                url: 'create_cart.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const selectedServices = [];
                        $("#form-step-2 input[type='checkbox'][name^='service_']:checked").each(function() {
                            const serviceId = String($(this).data('service-id') || '').trim();
                            if (serviceId) {
                                selectedServices.push(serviceId);
                            }
                        });
                        // Store summary for step 3
                        sessionStorage.setItem('medex_onboarding_summary', JSON.stringify({
                            provider_count: $("input[name='reminders_providers[]']:checked").length,
                            cart_id: response.cart_id,
                            total: response.total,
                            services: selectedServices
                        }));
                        location.href = 'onboarding.php?step=3';
                    } else if (!retried && response && response.error === "Invalid security token" && response.csrf_token) {
                        $('input[name="csrf_token_form"]').val(response.csrf_token);
                        submitStep2(true);
                    } else {
                        $("#result").html('<div class="alert alert-danger">' + response.error + '</div>');
                    }
                },
                error: function() {
                    $("#result").html('<div class="alert alert-danger">Failed to create cart. Please try again.</div>');
                }
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
                agreementModal.removeClass("show").attr("aria-hidden", "true");
                agreementFrame.attr("src", "about:blank");
                activeAgreementType = null;
            }

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

            function applyAgreementSignature(type, payload) {
                const signerName = String(payload.signer_name || "").trim();
                const signerTitle = String(payload.signer_title || "").trim();
                const signedAt = String(payload.signed_at || "").trim();
                if (!signerName || !signedAt) {
                    return;
                }
                if (type === "terms") {
                    $("#terms_signature_name").val(signerName);
                    $("#terms_signer_title").val(signerTitle);
                    $("#terms_signed_at").val(signedAt);
                    $("#terms_practice_name").val(String(payload.practice_name || "").trim());
                    $("#terms_legal_corporate_name").val(String(payload.legal_corporate_name || "").trim());
                } else if (type === "baa") {
                    $("#baa_signature_name").val(signerName);
                    $("#baa_signer_title").val(signerTitle);
                    $("#baa_signed_at").val(signedAt);
                    $("#baa_practice_name").val(String(payload.practice_name || "").trim());
                    $("#baa_legal_corporate_name").val(String(payload.legal_corporate_name || "").trim());
                }
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
                    return;
                }
            });
            $(document).on("keydown", function(e) {
                if (e.key === "Escape" && agreementModal.hasClass("show")) {
                    closeAgreementModal();
                }
            });

            if (wizardStep === 3) {
                $("#wizard-progress-fill").css("width", "100%");
            } else if (wizardStep === 2) {
                updateStep2Progress();
                $("#form-step-2 input[type='checkbox']").on("change", function() {
                    updateStep2Progress();
                });
                $("#form-step-2 .service-card").on("click", function(e) {
                    const $target = $(e.target);
                    if ($target.closest("input, a, button, select, textarea, label, .provider-list").length) {
                        return;
                    }
                    const $serviceToggle = $(this).children("input[type='checkbox'][name^='service_']").first();
                    if ($serviceToggle.length) {
                        $serviceToggle.prop("checked", !$serviceToggle.prop("checked")).trigger("change");
                    }
                });
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
                updateStep1SubmitState();
            });
            $("#email").on("input", function() {
                if (validateEmailField(false)) {
                    clearFieldError("#email", "#email-error");
                }
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
            });
            $("#otp_sms_destination").on("input", function() {
                clearFieldError("#otp_sms_destination", "#otp-sms-error");
            });
            $("#TERMS_yes, #BusAgree_yes").on("change", function() {
                updateStep1SubmitState();
            });
            updateOtpDestinationVisibility();
            syncAgreementCheckboxState();
            updateStep1SubmitState();

            if (window.location.search.includes('step=3')) {
                const serverCart = <?php echo json_encode(['items' => $sessionCartItems, 'total' => $sessionCartTotal]); ?>;
                let summary = {};
                try {
                    summary = JSON.parse(sessionStorage.getItem('medex_onboarding_summary') || '{}');
                } catch (e) {
                    summary = {};
                }
                if ((!Array.isArray(summary.services) || summary.services.length === 0) && Array.isArray(serverCart.items) && serverCart.items.length) {
                    summary.services = serverCart.items.map(function(item) {
                        return item && item.service ? item.service : '';
                    }).filter(Boolean);
                    if (typeof summary.total === "undefined" || summary.total === null || summary.total === "") {
                        summary.total = serverCart.total;
                    }
                }
                let html = '';
                const serviceLabels = <?php echo json_encode($serviceLabelMap); ?>;
                if (Array.isArray(summary.services) && summary.services.length) {
                    summary.services.forEach(function(serviceKey) {
                        const label = serviceLabels[serviceKey] || serviceKey;
                        if (serviceKey === 'appointment_reminders') {
                            const count = summary.provider_count || 0;
                            html += '<li>' + label + (count ? (' (' + count + ' ' + <?php echo json_encode(xl("providers")); ?> + ')') : '') + '</li>';
                        } else {
                            html += '<li>' + label + '</li>';
                        }
                    });
                } else {
                    if (Array.isArray(serverCart.items)) {
                        serverCart.items.forEach(function(item) {
                            const serviceKey = item && item.service ? item.service : '';
                            if (!serviceKey) {
                                return;
                            }
                            const label = serviceLabels[serviceKey] || serviceKey;
                            if (serviceKey === 'appointment_reminders') {
                                const count = summary.provider_count || Number(item.quantity || 0);
                                html += '<li>' + label + (count ? (' (' + count + ' ' + <?php echo json_encode(xl("providers")); ?> + ')') : '') + '</li>';
                            } else {
                                html += '<li>' + label + '</li>';
                            }
                        });
                    }
                }
                $("#summary-list").html(html || '<li>No services selected</li>');

                // Display total
                if (summary.total) {
                    $("#cart-total").text('$' + parseFloat(summary.total).toFixed(2) + ' / <?php echo xlj("month"); ?>');
                }

                // Initialize Braintree if we have a token
                initializeBraintree();
            }
        });

        function initializeBraintree() {
            // Get Braintree token from session via AJAX
            $.ajax({
                url: 'get_braintree_token.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const token = response && (response.clientToken || response.token);
                    if (response.success && token) {
                        setupBraintreeFields(token);
                    } else {
                        $("#payment-errors").text('Failed to initialize payment form: ' + (response.error || response.message || 'Unknown error')).show();
                    }
                },
                error: function() {
                    $("#payment-errors").text('Failed to initialize payment form').show();
                }
            });
        }

        function setupBraintreeFields(clientToken) {
            braintree.client.create({
                authorization: clientToken
            }, function(err, clientInstance) {
                if (err) {
                    console.error('Braintree client error:', err);
                    $("#payment-errors").text('Payment form initialization failed').show();
                    return;
                }

                braintree.hostedFields.create({
                    client: clientInstance,
                    styles: {
                        'input': {
                            'font-size': '15px',
                            'color': '#333'
                        }
                    },
                    fields: {
                        number: {
                            selector: '#card-number',
                            placeholder: '4111 1111 1111 1111'
                        },
                        cvv: {
                            selector: '#cvv',
                            placeholder: '123'
                        },
                        expirationDate: {
                            selector: '#expiration-date',
                            placeholder: 'MM/YY'
                        }
                    }
                }, function(err, hostedFieldsInstance) {
                    if (err) {
                        console.error('Hosted Fields error:', err);
                        $("#payment-errors").text('Payment form setup failed').show();
                        return;
                    }

                    // Handle form submission
                    $("#payment-form").on('submit', function(e) {
                        e.preventDefault();

                        const cardholderName = $("#cardholder-name").val();
                        if (!cardholderName) {
                            $("#payment-errors").text('Please enter cardholder name').show();
                            return;
                        }

                        $("#submit-payment").prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                        $("#payment-errors").hide();

                        hostedFieldsInstance.tokenize({
                            cardholderName: cardholderName
                        }, function(tokenizeErr, payload) {
                            if (tokenizeErr) {
                                console.error('Tokenization error:', tokenizeErr);
                                $("#payment-errors").text('Payment validation failed: ' + tokenizeErr.message).show();
                                $("#submit-payment").prop('disabled', false).html('<i class="fa fa-lock"></i> <?php echo xlj("Complete Payment & Activate"); ?>');
                                return;
                            }

                            // Submit payment to backend
                            processPayment(payload.nonce);
                        });
                    });
                });
            });
        }

        function processPayment(paymentNonce) {
            const summary = JSON.parse(sessionStorage.getItem('medex_onboarding_summary') || '{}');

            $.ajax({
                url: 'process_payment.php',
                type: 'POST',
                data: {
                    csrf_token_form: $('input[name="csrf_token_form"]').val(),
                    payment_nonce: paymentNonce,
                    cart_id: summary.cart_id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Payment successful - redirect to settings/dashboard
                        window.location.href = 'settings.php?payment_success=1';
                    } else {
                        $("#payment-errors").text('Payment failed: ' + (response.error || 'Unknown error')).show();
                        $("#submit-payment").prop('disabled', false).html('<i class="fa fa-lock"></i> <?php echo xlj("Complete Payment & Activate"); ?>');
                    }
                },
                error: function() {
                    $("#payment-errors").text('Payment processing failed. Please try again.').show();
                    $("#submit-payment").prop('disabled', false).html('<i class="fa fa-lock"></i> <?php echo xlj("Complete Payment & Activate"); ?>');
                }
            });
        }
    </script>
</body>
</html>
