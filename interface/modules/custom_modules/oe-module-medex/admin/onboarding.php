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
$whatsappOtpEnabled = MedExConfig::OTP_WHATSAPP_ENABLED;
$callbackTokenRow = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT gl_value FROM globals WHERE gl_name = 'medex_callback_token' LIMIT 1", []);
$callbackToken = trim($callbackTokenRow['gl_value'] ?? '');
$defaultCallbackUrl = '';
if (!empty($callbackToken) && !empty($_SERVER['HTTP_HOST'])) {
    $defaultCallbackUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/interface/modules/custom_modules/oe-module-medex/public/callback.php?token=' . rawurlencode($callbackToken);
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
        .wizard-header { text-align: center; margin-bottom: 40px; }
        .wizard-steps { display: flex; justify-content: space-between; margin-bottom: 40px; position: relative; }
        .wizard-steps::before { content: ''; position: absolute; top: 15px; left: 0; right: 0; height: 2px; background: #e0e0e0; z-index: 1; }
        .step { width: 30px; height: 30px; border-radius: 50%; background: #fff; border: 2px solid #e0e0e0; display: flex; align-items: center; justify-content: center; font-weight: bold; z-index: 2; position: relative; color: #999; }
        .step.active { border-color: #0f4b8f; color: #0f4b8f; }
        .step.completed { background: #0f4b8f; border-color: #0f4b8f; color: #white; }
        .step-label { position: absolute; top: 35px; font-size: 12px; width: 100px; text-align: center; left: 50%; transform: translateX(-50%); color: #999; }
        .step.active .step-label { color: #0f4b8f; font-weight: bold; }

        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #333; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 15px; }
        .form-control::placeholder { color: #9ca3af; opacity: 1; }
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

        #result { margin-top: 20px; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    </style>
</head>
<body>
    <div class="wizard-container">
        <div class="wizard-header">
            <a href="splash.php" style="float: left; color: #64748b; text-decoration: none; font-size: 14px; margin-top: 15px;">
                <i class="fa fa-arrow-left"></i> <?php echo xlt("Back to Overview"); ?>
            </a>
            <div style="font-size: 2rem; font-weight: 800; color: #0f4b8f; margin-bottom: 15px;">MedEx</div>
            <h2><?php echo xlt("Practice Onboarding"); ?></h2>
        </div>

        <div class="wizard-steps">
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
                </div>
                <div class="form-group">
                    <label for="password"><?php echo xlt("Password"); ?></label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="rpassword"><?php echo xlt("Confirm Password"); ?></label>
                    <input type="password" id="rpassword" name="rpassword" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="callback_url"><?php echo xlt("Callback URL (Required)"); ?></label>
                    <input type="url" id="callback_url" name="callback_url" class="form-control"
                           value="<?php echo attr($defaultCallbackUrl); ?>"
                           placeholder="https://your-domain/interface/modules/custom_modules/oe-module-medex/public/callback.php?token=..."
                           required>
                    <small style="color:#64748b;"><?php echo xlt("Production HTTPS endpoint required for auto-approval."); ?></small>
                </div>
                <div class="form-group">
                    <label style="font-weight:400;">
                        <input type="checkbox" id="production_confirm" name="production_confirm" value="1" required>
                        <?php echo xlt("This is a production-ready deployment (not a test/demo sandbox)"); ?>
                    </label>
                </div>
                <div class="form-group">
                    <label for="otp_channel"><?php echo xlt("Verification Channel"); ?></label>
                    <select id="otp_channel" name="otp_channel" class="form-control">
                        <option value="email"><?php echo xlt("Email OTP"); ?> - <?php echo xlt("House"); ?> $<?php echo text(number_format((float) MedExConfig::OTP_HOUSE_EMAIL_COST, 2)); ?></option>
                        <option value="sms"><?php echo xlt("SMS OTP"); ?> - <?php echo xlt("House Account"); ?> <?php echo text(MedExConfig::OTP_HOUSE_ACCOUNT_SMS); ?></option>
                        <option value="whatsapp" <?php echo !$whatsappOtpEnabled ? 'disabled' : ''; ?>>
                            <?php echo xlt("WhatsApp OTP"); ?><?php echo !$whatsappOtpEnabled ? ' (' . xlt("Coming Soon") . ')' : ''; ?> - <?php echo xlt("House Account"); ?> <?php echo text(MedExConfig::OTP_HOUSE_ACCOUNT_WHATSAPP); ?>
                        </option>
                    </select>
                    <small style="color:#64748b;"><?php echo xlt("WhatsApp OTP is scaffolded but disabled until provider connection is configured."); ?></small>
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-weight:400;">
                        <input type="checkbox" id="TERMS_yes" name="TERMS_yes" value="1" required>
                        <?php echo xlt("I have read and my practice agrees to the"); ?>
                        <a href="#" onclick="window.open('<?php echo attr_js($termsUrl); ?>','TERMS',800,600); return false;">
                            <?php echo xlt("MedEx Terms and Conditions"); ?>
                        </a>
                        (<?php echo xlt("Version"); ?> <?php echo text($termsVersion); ?>)
                    </label>
                </div>
                <div class="form-group">
                    <label style="font-weight:400;">
                        <input type="checkbox" id="BusAgree_yes" name="BusAgree_yes" value="1" required>
                        <?php echo xlt("I have read and accept the"); ?>
                        <a href="#" onclick="window.open('<?php echo attr_js($baaUrl); ?>','BusAssocAgree',800,600); return false;">
                            <?php echo xlt("MedEx Business Associate Agreement (BAA)"); ?>
                        </a>
                        (<?php echo xlt("Version"); ?> <?php echo text($baaVersion); ?>)
                    </label>
                </div>
                <div style="margin-top: 30px; text-align: right;">
                    <button type="button" class="btn btn-primary" onclick="submitStep1()"><?php echo xlt("Next: Configure Services"); ?> <i class="fa fa-arrow-right"></i></button>
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
        function submitStep1() {
            const email = $("#email").val();
            const password = $("#password").val();
            const rpassword = $("#rpassword").val();
            const callbackUrl = $("#callback_url").val();
            const productionConfirm = $("#production_confirm").is(':checked');
            const termsAgreed = $("#TERMS_yes").is(':checked');
            const baaAgreed = $("#BusAgree_yes").is(':checked');
            const otpChannel = $("#otp_channel").val();

            if (!email || !password || !callbackUrl) {
                alert("Please fill all required fields");
                return;
            }
            if (password !== rpassword) {
                alert("Passwords do not match");
                return;
            }
            if (!callbackUrl.startsWith("https://")) {
                alert("Callback URL must use HTTPS");
                return;
            }
            if (!productionConfirm) {
                alert("Production verification confirmation is required");
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

            $("#result").html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> Registering...</div>');

            $.ajax({
                url: 'register_process.php',
                type: 'POST',
                data: {
                    csrf_token_form: $('input[name="csrf_token_form"]').val(),
                    email: email,
                    password: password,
                    callback_url: callbackUrl,
                    production_confirm: '1',
                    TERMS_yes: '1',
                    BusAgree_yes: '1',
                    otp_channel: otpChannel,
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
                error: function() {
                    $("#result").html('<div class="alert alert-danger">Registration request failed</div>');
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
