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
$termsVersion = MedExConfig::TERMS_VERSION;
$baaVersion = MedExConfig::BAA_VERSION;
$termsUrl = MedExConfig::termsUrl();
$baaUrl = MedExConfig::baaUrl();
$privacyUrl = MedExConfig::privacyUrl();
$defaultOpenEmrUrl = '';
if (!empty($_SERVER['HTTP_HOST'])) {
    $webroot = trim((string)($GLOBALS['webroot'] ?? ''), '/');
    $defaultOpenEmrUrl = 'https://' . $_SERVER['HTTP_HOST'] . ($webroot !== '' ? '/' . $webroot : '');
}

// Fetch pricing from API for step 2
$pricing = $api->getPricing();
error_log('[ONBOARDING DEBUG] Pricing data: ' . print_r($pricing, true));

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
    <?php Header::setupHeader(['jquery-min-3-7-1']); ?>
    <link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/font-awesome-4.7.0/css/font-awesome.min.css">
    <?php if ($step == 3): ?>
    <script src="https://js.braintreegateway.com/web/3.97.2/js/client.min.js"></script>
    <script src="https://js.braintreegateway.com/web/3.97.2/js/hosted-fields.min.js"></script>
    <?php endif; ?>
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .wizard-container { max-width: 800px; margin: 50px auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
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
        .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 15px; }
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

        .service-card { border: 1px solid #eee; padding: 20px; border-radius: 8px; margin-bottom: 15px; display: flex; align-items: flex-start; gap: 15px; transition: all 0.2s; }
        .service-card:hover { border-color: #0f4b8f; background: #f9faff; }
        .service-info { flex: 1; }
        .service-title { font-weight: bold; font-size: 16px; margin-bottom: 5px; }
        .service-desc { font-size: 13px; color: #666; }
        .service-price { font-size: 14px; color: #0f4b8f; font-weight: 600; margin-top: 8px; }

        .provider-list { max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 6px; margin-top: 10px; }
        .provider-item { display: flex; align-items: center; gap: 10px; padding: 5px 0; border-bottom: 1px solid #f5f5f5; }
        .onboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 8px; }
        .panel-card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px; background: #ffffff; }
        .panel-card .form-group:last-child { margin-bottom: 0; }
        .full-width-card { margin-top: 14px; }

        #result { margin-top: 20px; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .otp-panel { border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px; margin-top: 10px; background: #f8fafc; }
        .otp-inline { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-top: 10px; }
        .otp-status { font-size: 13px; color: #475569; margin-top: 8px; }
        .otp-status.ok { color: #15803d; }
        .otp-status.err { color: #b91c1c; }
        .field-status { font-size: 12px; margin-top: 6px; color: #475569; }
        .field-status.ok { color: #15803d; }
        .field-status.err { color: #b91c1c; }
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
                <input type="hidden" name="csrf_token_form" value="<?php echo attr((string) CsrfUtils::collectCsrfToken(session: $session)); ?>" />
                <div class="form-group">
                    <label for="email"><?php echo xlt("Administrator E-mail"); ?></label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="admin@practice.com" required>
                    <small style="color:#64748b; display:block; margin-top:6px;"><?php echo xlt("This email will be your username for signing in to MedEx."); ?></small>
                    <div id="email-error" class="field-error"><?php echo xlt("Please enter a valid administrator email address."); ?></div>
                </div>
                <div class="onboard-grid">
                    <div class="panel-card">
                        <div class="form-group">
                            <label for="password"><?php echo xlt("Password"); ?></label>
                            <div class="password-wrap">
                                <input type="password" id="password" name="password" class="form-control" required style="padding-right:40px;">
                                <i class="fa fa-eye-slash password-toggle" id="toggle-password" title="<?php echo xla("Show/Hide Password"); ?>"></i>
                            </div>
                            <small style="color:#64748b; display:block; margin-top:6px;"><?php echo xlt("Use at least 8 characters with uppercase, lowercase, number, and special character."); ?></small>
                            <div id="password-error" class="field-error"><?php echo xlt("Password must be at least 8 characters and include uppercase, lowercase, number, and special character."); ?></div>
                        </div>
                    </div>
                    <div class="panel-card">
                        <div class="form-group">
                            <label for="rpassword"><?php echo xlt("Confirm Password"); ?></label>
                            <div class="password-wrap">
                                <input type="password" id="rpassword" name="rpassword" class="form-control" required style="padding-right:40px;">
                                <i class="fa fa-eye-slash password-toggle" id="toggle-rpassword" title="<?php echo xla("Show/Hide Password"); ?>"></i>
                            </div>
                        </div>
                    </div>
                    <div class="panel-card">
                        <div class="form-group">
                            <label for="callback_url"><?php echo xlt("OpenEMR URL (Required)"); ?></label>
                            <input type="url" id="callback_url" name="callback_url" class="form-control"
                                   value="<?php echo attr($defaultOpenEmrUrl); ?>"
                                   placeholder="https://your-openemr-domain.com"
                                   required>
                            <small style="color:#64748b;"><?php echo xlt("Enter the url we can use to reach your OpenEMR server."); ?></small>
                            <div id="callback-error" class="field-error"><?php echo xlt("OpenEMR URL must be a valid public HTTPS URL."); ?></div>
                            <div id="callback-status" class="field-status"></div>
                        </div>
                    </div>
                    <div class="panel-card">
                        <div class="form-group">
                            <label for="otp_channel"><?php echo xlt("One-Time Password (OTP) Method"); ?></label>
                            <select id="otp_channel" name="otp_channel" class="form-control">
                                <option value="email"><?php echo xlt("Email One-Time Password (OTP)"); ?></option>
                                <option value="sms"><?php echo xlt("SMS One-Time Password (OTP)"); ?></option>
                            </select>
                            <small style="color:#64748b;">
                                <?php echo xlt("We use a one-time password to verify your identity before enabling your MedEx setup."); ?>
                                <a href="#" onclick="window.open('<?php echo attr_js($privacyUrl); ?>','PrivacyPolicy',900,700); return false;"><?php echo xlt("Privacy Policy"); ?></a>
                            </small>
                            <small style="color:#64748b; display:block; margin-top:4px;"><?php echo xlt("SMS OTP currently supports U.S./Canada numbers only."); ?></small>
                            <?php // SMS/WhatsApp OTP intentionally hidden in UI until end-to-end destination + verification flow is implemented. ?>
                            <div id="otp-sms-destination-wrap" class="form-group" style="display:none; margin-top: 10px;">
                                <label for="otp_sms_destination"><?php echo xlt("Mobile Number for SMS OTP"); ?></label>
                                <input type="tel" id="otp_sms_destination" name="otp_sms_destination" class="form-control"
                                       placeholder="+15551234567">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-card full-width-card">
                    <div class="service-title" style="margin-bottom: 6px;"><?php echo xlt("Send One-Time Password"); ?></div>
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
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label style="font-weight:400; margin-bottom: 0;">
                            <input type="checkbox" id="comms_consent" name="comms_consent" value="1" required>
                            <?php echo xlt("I agree to receive onboarding and account-related emails and text messages from MedEx."); ?>
                        </label>
                        <small style="color:#64748b; display:block; margin-top:6px;"><?php echo xlt("Message and data rates may apply for SMS."); ?></small>
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label style="font-weight:400; margin-bottom: 0;">
                            <input type="checkbox" id="TERMS_yes" name="TERMS_yes" value="1" required>
                            <?php echo xlt("I have read and my practice agrees to the"); ?>
                            <a href="#" onclick="window.open('<?php echo attr_js($termsUrl); ?>','TERMS',800,600); return false;">
                                <?php echo xlt("MedEx Terms and Conditions"); ?>
                            </a>
                            (<?php echo xlt("Version"); ?> <?php echo text($termsVersion); ?>)
                        </label>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-weight:400; margin-bottom: 0;">
                            <input type="checkbox" id="BusAgree_yes" name="BusAgree_yes" value="1" required>
                            <?php echo xlt("I have read and accept the"); ?>
                            <a href="#" onclick="window.open('<?php echo attr_js($baaUrl); ?>','BusAssocAgree',800,600); return false;">
                                <?php echo xlt("MedEx Business Associate Agreement (BAA)"); ?>
                            </a>
                            (<?php echo xlt("Version"); ?> <?php echo text($baaVersion); ?>)
                        </label>
                    </div>
                </div>
                <div style="margin-top: 30px; text-align: right;">
                    <button type="button" id="step1-next-btn" class="btn btn-primary" onclick="submitStep1()" disabled><?php echo xlt("Next: Configure Services"); ?> <i class="fa fa-arrow-right"></i></button>
                </div>
            </form>

        <?php elseif ($step == 2): ?>
            <!-- Step 2: Service Configuration -->
            <form id="form-step-2">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr((string) CsrfUtils::collectCsrfToken(session: $session)); ?>" />

                <p><?php echo xlt("Select the services you wish to enable for your practice. You can start with a trial for any provider-based service."); ?></p>

                <!-- Reminders & Recalls -->
                <div class="service-card">
                    <input type="checkbox" name="service_reminders" id="service_reminders" checked>
                    <div class="service-info">
                        <div class="service-title"><?php echo xlt("Reminders & Recalls"); ?></div>
                        <div class="service-desc"><?php echo xlt("Automated appointment reminders (SMS/Email/Voice) and comprehensive Recall Board management."); ?></div>
                        <div class="service-price">
                            <?php
                            $reminderTrial = $pricing['services']['appointment_reminders']['trial'] ?? null;
                            if ($reminderTrial && $reminderTrial['enabled']) {
                                echo "<span style='color: #28a745; font-weight: 600;'>" . xlt("Trial:") . " ";
                                if ($reminderTrial['price'] == 0) {
                                    echo $reminderTrial['duration'] . " " . xlt($reminderTrial['frequency']) . ($reminderTrial['duration'] > 1 ? "s" : "") . " " . xlt("free");
                                } else {
                                    echo "$" . number_format($reminderTrial['price'], 2) . " / " . xlt($reminderTrial['frequency']) . " " . xlt("for") . " " . $reminderTrial['duration'] . " " . xlt($reminderTrial['frequency']) . ($reminderTrial['duration'] > 1 ? "s" : "");
                                }
                                echo "</span><br>";
                                echo "<span style='font-size: 0.9em;'>" . xlt("Then") . " $" . number_format($pricing['services']['appointment_reminders']['price'] ?? 9.95, 2) . " / " . xlt($pricing['services']['appointment_reminders']['unit'] ?? 'mo per provider') . "</span>";
                            } else {
                                echo "$" . number_format($pricing['services']['appointment_reminders']['price'] ?? 9.95, 2) . " / " . xlt($pricing['services']['appointment_reminders']['unit'] ?? 'mo per provider');
                            }
                            ?>
                        </div>

                        <div id="provider-selection-reminders" style="margin-top: 15px;">
                            <label><?php echo xlt("Select Providers for Reminders"); ?>:</label>
                            <div class="provider-list">
                                <?php
                                $res = sqlStatement("SELECT id, fname, lname FROM users WHERE authorized=1 AND active=1 ORDER BY lname");
                                while ($row = sqlFetchArray($res)) {
                                    echo "<div class='provider-item'><input type='checkbox' name='reminders_providers[]' value='{$row['id']}'> {$row['lname']}, {$row['fname']}</div>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar View/Export -->
                <div class="service-card">
                    <input type="checkbox" name="service_calendar_view" id="service_calendar_view">
                    <div class="service-info">
                        <div class="service-title"><?php echo xlt("Calendar View & Export"); ?></div>
                        <div class="service-desc"><?php echo xlt("Read-only web calendar with export capabilities for external scheduling systems."); ?></div>
                        <div class="service-price">
                            <?php
                            $calViewTrial = $pricing['services']['calendar_view']['trial'] ?? null;
                            if ($calViewTrial && $calViewTrial['enabled']) {
                                echo "<span style='color: #28a745; font-weight: 600;'>" . xlt("Trial:") . " ";
                                if ($calViewTrial['price'] == 0) {
                                    echo $calViewTrial['duration'] . " " . xlt($calViewTrial['frequency']) . ($calViewTrial['duration'] > 1 ? "s" : "") . " " . xlt("free");
                                } else {
                                    echo "$" . number_format($calViewTrial['price'], 2) . " / " . xlt($calViewTrial['frequency']) . " " . xlt("for") . " " . $calViewTrial['duration'] . " " . xlt($calViewTrial['frequency']) . ($calViewTrial['duration'] > 1 ? "s" : "");
                                }
                                echo "</span><br>";
                                echo "<span style='font-size: 0.9em;'>" . xlt("Then") . " $" . number_format($pricing['services']['calendar_view']['price'] ?? 0.95, 2) . " / " . xlt($pricing['services']['calendar_view']['unit'] ?? '/mo per provider') . "</span>";
                            } else {
                                echo "$" . number_format($pricing['services']['calendar_view']['price'] ?? 0.95, 2) . " / " . xlt($pricing['services']['calendar_view']['unit'] ?? '/mo per provider');
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Calendar & AI Rescheduler -->
                <div class="service-card">
                    <input type="checkbox" name="service_calendar_ai" id="service_calendar_ai">
                    <div class="service-info">
                        <div class="service-title"><?php echo xlt("Calendar & AI Rescheduler"); ?></div>
                        <div class="service-desc"><?php echo xlt("Modern web-based calendar and AI-powered automated patient rescheduling."); ?></div>
                        <div class="service-price">
                            <?php
                            $calendarTrial = $pricing['services']['calendar_ai']['trial'] ?? null;
                            if ($calendarTrial && $calendarTrial['enabled']) {
                                echo "<span style='color: #28a745; font-weight: 600;'>" . xlt("Trial:") . " ";
                                if ($calendarTrial['price'] == 0) {
                                    echo $calendarTrial['duration'] . " " . xlt($calendarTrial['frequency']) . ($calendarTrial['duration'] > 1 ? "s" : "") . " " . xlt("free");
                                } else {
                                    echo "$" . number_format($calendarTrial['price'], 2) . " / " . xlt($calendarTrial['frequency']) . " " . xlt("for") . " " . $calendarTrial['duration'] . " " . xlt($calendarTrial['frequency']) . ($calendarTrial['duration'] > 1 ? "s" : "");
                                }
                                echo "</span><br>";
                                echo "<span style='font-size: 0.9em;'>" . xlt("Then") . " $" . number_format($pricing['services']['calendar_ai']['price'] ?? 4.95, 2) . " / " . xlt($pricing['services']['calendar_ai']['unit'] ?? 'mo per provider + usage') . "</span>";
                            } else {
                                echo "$" . number_format($pricing['services']['calendar_ai']['price'] ?? 4.95, 2) . " / " . xlt($pricing['services']['calendar_ai']['unit'] ?? 'mo per provider + usage');
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Secure Chat -->
                <div class="service-card">
                    <input type="checkbox" name="service_chat" id="service_chat">
                    <div class="service-info">
                        <div class="service-title"><?php echo xlt("Secure Chat (Practice-wide)"); ?></div>
                        <div class="service-desc"><?php echo xlt("HIPAA-compliant two-way messaging for all staff and patients."); ?></div>
                        <div class="service-price">
                            <?php
                            $chatTrial = $pricing['services']['secure_chat']['trial'] ?? null;
                            if ($chatTrial && $chatTrial['enabled']) {
                                echo "<span style='color: #28a745; font-weight: 600;'>" . xlt("Trial:") . " ";
                                if ($chatTrial['price'] == 0) {
                                    echo $chatTrial['duration'] . " " . xlt($chatTrial['frequency']) . ($chatTrial['duration'] > 1 ? "s" : "") . " " . xlt("free");
                                } else {
                                    echo "$" . number_format($chatTrial['price'], 2) . " / " . xlt($chatTrial['frequency']) . " " . xlt("for") . " " . $chatTrial['duration'] . " " . xlt($chatTrial['frequency']) . ($chatTrial['duration'] > 1 ? "s" : "");
                                }
                                echo "</span><br>";
                                echo "<span style='font-size: 0.9em;'>" . xlt("Then") . " $" . number_format($pricing['services']['secure_chat']['price'] ?? 4.95, 2) . xlt($pricing['services']['secure_chat']['unit'] ?? '/mo') . "</span>";
                            } else {
                                echo "$" . number_format($pricing['services']['secure_chat']['price'] ?? 4.95, 2) . xlt($pricing['services']['secure_chat']['unit'] ?? '/mo');
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- PDF Form Management -->
                <div class="service-card">
                    <input type="checkbox" name="service_pdf" id="service_pdf">
                    <div class="service-info">
                        <div class="service-title"><?php echo xlt("PDF Form Management"); ?></div>
                        <div class="service-desc"><?php echo xlt("Digital form filling, signature capture, and AI data extraction."); ?></div>
                        <div class="service-price">
                            <?php
                            $pdfTrial = $pricing['services']['pdf_management']['trial'] ?? null;
                            if ($pdfTrial && $pdfTrial['enabled']) {
                                echo "<span style='color: #28a745; font-weight: 600;'>" . xlt("Trial:") . " ";
                                if ($pdfTrial['price'] == 0) {
                                    echo $pdfTrial['duration'] . " " . xlt($pdfTrial['frequency']) . ($pdfTrial['duration'] > 1 ? "s" : "") . " " . xlt("free");
                                } else {
                                    echo "$" . number_format($pdfTrial['price'], 2) . " / " . xlt($pdfTrial['frequency']) . " " . xlt("for") . " " . $pdfTrial['duration'] . " " . xlt($pdfTrial['frequency']) . ($pdfTrial['duration'] > 1 ? "s" : "");
                                }
                                echo "</span><br>";
                                echo "<span style='font-size: 0.9em;'>" . xlt("Then") . " $" . number_format($pricing['services']['pdf_management']['price'] ?? 4.95, 2) . xlt($pricing['services']['pdf_management']['unit'] ?? '/mo') . "</span>";
                            } else {
                                echo "$" . number_format($pricing['services']['pdf_management']['price'] ?? 4.95, 2) . xlt($pricing['services']['pdf_management']['unit'] ?? '/mo');
                            }
                            ?>
                        </div>
                    </div>
                </div>

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
                        <input type="hidden" name="csrf_token_form" value="<?php echo attr((string) CsrfUtils::collectCsrfToken(session: $session)); ?>" />
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
        let callbackValidated = false;

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
            }
            otpVerified = false;
            $("#otp_proof").val("");
            $("#otp_code").val("");
            setOtpStatus("Send and verify your one-time password before continuing.");
            updateStep1SubmitState();
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
                return /^\+\d{10,15}$/.test((sms || "").trim());
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

        function setCallbackStatus(message, kind = '') {
            const el = $("#callback-status");
            el.removeClass('ok err');
            if (kind) {
                el.addClass(kind);
            }
            el.text(message || '');
        }

        function validateOpenEmrUrlFormat(showMessage = true) {
            const value = ($("#callback_url").val() || "").trim();
            const valid = /^https:\/\/[^\s/$.?#].[^\s]*$/i.test(value) &&
                !/^https:\/\/(localhost|127\.|10\.|192\.168\.|172\.(1[6-9]|2\d|3[0-1])\.)/i.test(value);
            if (valid) {
                clearFieldError("#callback_url", "#callback-error");
                return true;
            }
            if (showMessage) {
                setFieldError("#callback_url", "#callback-error", "OpenEMR URL must be a valid public HTTPS URL.");
            }
            return false;
        }

        function updateStep1SubmitState() {
            const accountReady = validateEmailField(false) &&
                validatePasswordField(false) &&
                ($("#password").val() || "") === ($("#rpassword").val() || "");
            const agreementsReady = $("#TERMS_yes").is(':checked') &&
                $("#BusAgree_yes").is(':checked') &&
                $("#comms_consent").is(':checked');
            const canSubmit = accountReady && callbackValidated && otpVerified && agreementsReady;
            $("#step1-next-btn").prop("disabled", !canSubmit);
            updateStep1Progress(accountReady, callbackValidated, otpVerified, agreementsReady);
        }

        function updateStep1Progress(accountReady, urlReady, otpReady, agreementsReady) {
            const completed = (accountReady ? 1 : 0) + (urlReady ? 1 : 0) + (otpReady ? 1 : 0) + (agreementsReady ? 1 : 0);
            const pct = Math.round((completed / 4) * 100);
            $("#wizard-progress-fill").css("width", pct + "%");
        }

        function validateCallbackFromApi() {
            callbackValidated = false;
            updateStep1SubmitState();
            if (!validateOpenEmrUrlFormat(true)) {
                setCallbackStatus("", "");
                return;
            }

            const csrf = $('input[name="csrf_token_form"]').val();
            const callbackUrl = ($("#callback_url").val() || "").trim();
            setCallbackStatus("Checking install from api.hipaabank.net...", "");
            ensureActiveSession();

            $.ajax({
                url: 'onboarding_validate_url.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    csrf_token_form: csrf,
                    callback_url: callbackUrl
                },
                success: function(response) {
                    if (response.success) {
                        callbackValidated = true;
                        clearFieldError("#callback_url", "#callback-error");
                        setCallbackStatus(response.message || "OpenEMR URL verified.", "ok");
                    } else {
                        callbackValidated = false;
                        setFieldError("#callback_url", "#callback-error", response.error || "Unable to verify install");
                        setCallbackStatus("Unable to verify install from api.hipaabank.net.", "err");
                    }
                    updateStep1SubmitState();
                },
                error: function(jqXHR) {
                    callbackValidated = false;
                    setFieldError("#callback_url", "#callback-error", ajaxErrorMessage(jqXHR, "Unable to verify install from api.hipaabank.net"));
                    setCallbackStatus("Unable to verify install from api.hipaabank.net.", "err");
                    updateStep1SubmitState();
                }
            });
        }

        function sendOtp() {
            const channel = $("#otp_channel").val();
            const email = ($("#email").val() || "").trim();
            const sms = ($("#otp_sms_destination").val() || "").trim();
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
                        setOtpStatus(response.message || "One-time password sent. Check your selected channel.", "ok");
                    } else {
                        setOtpStatus(response.error || "Unable to send one-time password.", "err");
                    }
                },
                error: function() {
                    setOtpStatus(ajaxErrorMessage(arguments[0], "Unable to send one-time password due to a request error."), "err");
                }
            });
        }

        function verifyOtp() {
            const code = ($("#otp_code").val() || "").trim();
            const csrf = $('input[name="csrf_token_form"]').val();
            const channel = $("#otp_channel").val();
            const email = ($("#email").val() || "").trim();
            const sms = ($("#otp_sms_destination").val() || "").trim();

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
            const commsConsent = $("#comms_consent").is(':checked');
            const otpChannel = $("#otp_channel").val();
            const otpProof = ($("#otp_proof").val() || "").trim();
            const otpSmsDestination = ($("#otp_sms_destination").val() || "").trim();

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
            if (!callbackValidated) {
                setFieldError("#callback_url", "#callback-error", "Verify your OpenEMR URL before continuing.");
                setCallbackStatus("OpenEMR URL has not been verified from api.hipaabank.net.", "err");
                return;
            }
            if (password !== rpassword) {
                alert("Passwords do not match");
                return;
            }
            if (!termsAgreed) {
                alert("You must agree to the Terms & Conditions before signing up");
                return;
            }
            if (!baaAgreed) {
                alert("You must agree to the HIPAA Business Associate Agreement before signing up");
                return;
            }
            if (!commsConsent) {
                alert("You must agree to receive onboarding and account-related messages from MedEx");
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
                    comms_consent: '1',
                    otp_channel: otpChannel,
                    otp_sms_destination: otpSmsDestination,
                    otp_proof: otpProof,
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

        function submitStep2() {
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
                        // Store summary for step 3
                        sessionStorage.setItem('medex_onboarding_summary', JSON.stringify({
                            reminders: $("#service_reminders").is(':checked'),
                            calendar: $("#service_calendar").is(':checked'),
                            chat: $("#service_chat").is(':checked'),
                            pdf: $("#service_pdf").is(':checked'),
                            provider_count: $("input[name='reminders_providers[]']:checked").length,
                            cart_id: response.cart_id,
                            total: response.total
                        }));
                        location.href = 'onboarding.php?step=3';
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
                updateStep1SubmitState();
            });
            $("#password").on("input", function() {
                if (validatePasswordField(false)) {
                    clearFieldError("#password", "#password-error");
                }
                updateStep1SubmitState();
            });
            $("#rpassword").on("input blur", function() {
                updateStep1SubmitState();
            });
            $("#callback_url").on("input", function() {
                callbackValidated = false;
                clearFieldError("#callback_url", "#callback-error");
                setCallbackStatus("", "");
                updateStep1SubmitState();
            });
            $("#callback_url").on("blur", function() {
                validateCallbackFromApi();
            });
            $("#TERMS_yes, #BusAgree_yes, #comms_consent").on("change", function() {
                updateStep1SubmitState();
            });
            updateOtpDestinationVisibility();
            updateStep1SubmitState();

            if (window.location.search.includes('step=3')) {
                const summary = JSON.parse(sessionStorage.getItem('medex_onboarding_summary') || '{}');
                let html = '';
                if (summary.reminders) html += '<li>' + <?php echo xlj("Reminders & Recalls"); ?> + ' (' + summary.provider_count + ' ' + <?php echo xlj("providers"); ?> + ')</li>';
                if (summary.calendar) html += '<li>' + <?php echo xlj("Calendar & AI Rescheduler"); ?> + '</li>';
                if (summary.chat) html += '<li>' + <?php echo xlj("Secure Chat"); ?> + '</li>';
                if (summary.pdf) html += '<li>' + <?php echo xlj("PDF Form Management"); ?> + '</li>';
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
                    if (response.success && response.token) {
                        setupBraintreeFields(response.token);
                    } else {
                        $("#payment-errors").text('Failed to initialize payment form: ' + (response.error || 'Unknown error')).show();
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
