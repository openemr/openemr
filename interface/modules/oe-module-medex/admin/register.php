<?php
/**
 * MedEx Module - Registration/Signup
 *
 * Two-stage registration process:
 * Stage 1: Overview of MedEx services
 * Stage 2: Account creation form
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("MedEx Registration")]);
    exit;
}

$stage = $_GET['stage'] ?? '1';
$siteId = $_GET['site'] ?? 'default';
$csrfToken = CsrfUtils::collectCsrfToken();

// Check if already registered (but still allow re-registration/reconnection)
$existing = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT ME_api_key, MedEx_id, ME_username FROM medex_prefs WHERE ME_api_key IS NOT NULL AND ME_api_key != '' LIMIT 1", []);
$already_registered = !empty($existing['ME_api_key']) && !empty($existing['MedEx_id']);
$existing_email = $existing['ME_username'] ?? '';
$callbackTokenRow = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT gl_value FROM globals WHERE gl_name = 'medex_callback_token' LIMIT 1", []);
$callbackToken = trim($callbackTokenRow['gl_value'] ?? '');
$defaultCallbackUrl = '';
if (!empty($callbackToken) && !empty($_SERVER['HTTP_HOST'])) {
    $defaultCallbackUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/interface/modules/custom_modules/oe-module-medex/public/callback.php?token=' . rawurlencode($callbackToken);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt("MedEx Registration"); ?></title>
    <?php Header::setupHeader(['jquery-min-3-7-1']); ?>
    <style>
        body {
            background: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #0f4b8f;
            text-align: center;
            border-bottom: 3px solid #0f4b8f;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .jumbotron {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .feature-section {
            margin: 30px 0;
        }
        .feature-section h3 {
            color: #0f4b8f;
            border-bottom: 2px solid #0f4b8f;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .list-group {
            margin: 20px 0;
        }
        .list-group-item {
            padding: 12px 20px;
            border: 1px solid #dee2e6;
            margin-bottom: 5px;
            border-radius: 4px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-control:focus {
            outline: none;
            border-color: #0f4b8f;
            box-shadow: 0 0 0 3px rgba(15, 75, 143, 0.1);
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #0f4b8f;
            color: white;
        }
        .btn-primary:hover {
            background: #0a3460;
        }
        .text-center {
            text-align: center;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .signup_help {
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
        }
        .nodisplay {
            display: none;
        }
        .text-success {
            color: #28a745;
        }
        .top_right_corner {
            float: right;
            margin-top: -30px;
        }
        .checkbox-group {
            margin: 15px 0;
        }
        .checkbox-group label {
            font-weight: normal;
            margin-left: 5px;
        }
        input[type="checkbox"] {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($already_registered && $stage == '1'): ?>
            <!-- Show reconnect option on stage 1 only -->
            <div class="alert alert-info">
                <strong><i class="fa fa-check-circle"></i> <?php echo xlt("Already Registered"); ?></strong><br>
                <?php echo xlt("Your practice is already registered with MedEx"); ?>.
                <br><br>
                <strong><?php echo xlt("Practice ID"); ?>:</strong> <?php echo text($existing['MedEx_id']); ?><br>
                <strong><?php echo xlt("Registered Email"); ?>:</strong> <?php echo text($existing_email); ?><br>
                <strong><?php echo xlt("API Key"); ?>:</strong> <?php echo text(substr($existing['ME_api_key'], 0, 20) . '...'); ?>
                <br><br>
                <a href="index.php" class="btn btn-primary">
                    <i class="fa fa-tachometer-alt"></i> <?php echo xlt("Go to Dashboard"); ?>
                </a>
                <a href="settings.php" class="btn btn-secondary">
                    <i class="fa fa-cog"></i> <?php echo xlt("Settings"); ?>
                </a>
                <a href="reconnect.php" class="btn btn-warning">
                    <i class="fa fa-refresh"></i> <?php echo xlt("Reconnect/Re-register"); ?>
                </a>
            </div>
        <?php elseif ($stage == '1'): ?>
            <!-- Stage 1: Service Overview -->
            <h2><?php echo xlt("Welcome to MedEx"); ?></h2>

            <div class="jumbotron">
                <div class="alert alert-warning">
                    <strong><i class="fa fa-shield"></i> <?php echo xlt("Production Verification Required"); ?>:</strong><br>
                    <?php echo xlt("MedEx is a SaaS service for live practices. Public HTTPS callback URL and production readiness are required for activation."); ?>
                </div>
                <p style="font-size: 16px; line-height: 1.6; text-align: center;">
                    <?php echo xlt("MedEx provides comprehensive patient communication and practice management tools integrated directly with OpenEMR"); ?>.
                </p>

                <div class="feature-section">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="border-bottom"><?php echo xlt('Targets'); ?>:</h3>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><i class="fa fa-check"></i> <?php echo xlt('Appointment Reminders'); ?></li>
                                <li class="list-group-item"><i class="fa fa-check"></i> <?php echo xlt('Patient Recalls'); ?></li>
                                <li class="list-group-item"><i class="fa fa-check"></i> <?php echo xlt('Office Announcements'); ?></li>
                                <li class="list-group-item"><i class="fa fa-check"></i> <?php echo xlt('Patient Surveys'); ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h3 class="border-bottom"><?php echo xlt('Channels'); ?>:</h3>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><i class="fa fa-comment"></i> <?php echo xlt('SMS Messages'); ?></li>
                                <li class="list-group-item"><i class="fa fa-phone"></i> <?php echo xlt('Voice Messages'); ?></li>
                                <li class="list-group-item"><i class="fa fa-envelope"></i> <?php echo xlt('E-mail Messaging'); ?></li>
                                <li class="list-group-item"><i class="fa fa-mail-bulk"></i> <?php echo xlt('Postcards'); ?></li>
                                <li class="list-group-item"><i class="fa fa-tag"></i> <?php echo xlt('Address Labels'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="text-center" style="margin-top: 30px;">
                    <a href="register.php?stage=2&amp;site=<?php echo attr_url($siteId); ?>" class="btn btn-primary">
                        <i class="fa fa-arrow-right"></i> <?php echo xlt('Sign-up'); ?>
                    </a>
                </div>
            </div>

        <?php elseif ($stage == '2'): ?>
            <!-- Stage 2: Registration Form -->
            <h2><?php echo xlt('Register'); ?>: MedEx</h2>

            <div class="alert alert-info">
                <strong><i class="fa fa-info-circle"></i> <?php echo xlt("Note"); ?>:</strong><br>
                <?php echo xlt("This registration process creates your account with the MedEx messaging service"); ?>.
                <?php echo xlt("If you already have an account, simply enter your existing credentials to reconnect"); ?>.
            </div>

            <form name="medex_start" id="medex_start" class="jumbotron">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>" />

                <div id="setup_1">
                    <div id="answer" name="answer">
                        <div class="form-group">
                            <label for="new_email">
                                <?php echo xlt('E-mail'); ?>:
                                <i id="email_check" name="email_check" class="top_right_corner nodisplay text-success fa fa-check"></i>
                            </label>
                            <input type="email"
                                   class="form-control"
                                   id="new_email"
                                   name="new_email"
                                   value="<?php echo attr($GLOBALS['user_data']['email'] ?? ''); ?>"
                                   placeholder="<?php echo xla('your email address'); ?>"
                                   required />
                            <div class="signup_help nodisplay" id="email_help">
                                <?php echo xlt('Please provide a valid e-mail address to proceed'); ?>...
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_password">
                                <?php echo xlt('Password'); ?>:
                                <i id="pwd_check" name="pwd_check" class="top_right_corner nodisplay text-success fa fa-check"></i>
                            </label>
                            <div style="position: relative;">
                                <input type="password"
                                       placeholder="<?php echo xla('Password'); ?>"
                                       id="new_password"
                                       name="new_password"
                                       class="form-control"
                                       style="padding-right: 40px;"
                                       required />
                                <i class="fa fa-eye-slash" id="togglePassword"
                                   style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666;"
                                   title="<?php echo xla('Show/Hide Password'); ?>"></i>
                            </div>
                            <div id="pwd_help" class="nodisplay signup_help">
                                <?php echo xlt('Secure Password Required') . ": " . xlt('8-12 characters long, including at least one upper case letter, one lower case letter, one number, one special character and no common strings'); ?>...
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_rpassword">
                                <?php echo xlt('Repeat'); ?>:
                                <i id="pwd_rcheck" name="pwd_rcheck" class="top_right_corner nodisplay text-success fa fa-check"></i>
                            </label>
                            <div style="position: relative;">
                                <input type="password"
                                       placeholder="<?php echo xla('Repeat password'); ?>"
                                       id="new_rpassword"
                                       name="new_rpassword"
                                       class="form-control"
                                       style="padding-right: 40px;"
                                       required />
                                <i class="fa fa-eye-slash" id="togglePasswordRepeat"
                                   style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666;"
                                   title="<?php echo xla('Show/Hide Password'); ?>"></i>
                            </div>
                            <div id="pwd_rhelp" class="nodisplay signup_help">
                                <?php echo xlt('Passwords do not match.'); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="callback_url">
                                <?php echo xlt('Callback URL (Required)'); ?>:
                            </label>
                            <input type="url"
                                   class="form-control"
                                   id="callback_url"
                                   name="callback_url"
                                   value="<?php echo attr($defaultCallbackUrl); ?>"
                                   placeholder="<?php echo xla('https://your-domain/interface/modules/custom_modules/oe-module-medex/public/callback.php?token=...'); ?>"
                                   required />
                            <div class="signup_help">
                                <?php echo xlt('Must be a production HTTPS endpoint. Private/local/test URLs are not auto-approved.'); ?>
                            </div>
                        </div>

                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="TERMS_yes" name="TERMS_yes" required />
                        <label for="TERMS_yes">
                            <?php echo xlt('I have read and my practice agrees to the'); ?>
                            <a href="#" onclick="window.open('<?php echo \OpenEMR\Modules\MedEx\MedExConfig::publicBaseUrl(); ?>/index.php?route=information/information&information_id=5','TERMS',800,600); return false;">
                                MedEx <?php echo xlt('Terms and Conditions'); ?>
                            </a>
                        </label>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="BusAgree_yes" name="BusAgree_yes" required />
                        <label for="BusAgree_yes">
                            <?php echo xlt('I have read and accept the'); ?>
                            <a href="#" onclick="window.open('<?php echo \OpenEMR\Modules\MedEx\MedExConfig::publicBaseUrl(); ?>/index.php?route=information/information&information_id=8','Bus Assoc Agree',800,600); return false;">
                                MedEx <?php echo xlt('Business Associate Agreement'); ?>
                            </a>
                        </label>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="Production_yes" name="Production_yes" required />
                        <label for="Production_yes">
                            <?php echo xlt('I confirm this is a production-ready deployment with a publicly reachable HTTPS callback endpoint'); ?>.
                        </label>
                    </div>

                    <div class="text-center" style="margin-top: 30px;">
                        <button type="button" id="Register" class="btn btn-primary" onclick="signUp();">
                            <i class="fa fa-user-plus"></i> <?php echo xlt('Register'); ?>
                        </button>
                    </div>

                    <div id="result" style="margin-top: 20px;"></div>
                </div>
            </form>

        <?php endif; ?>
    </div>

    <script>
    function validateEmail(email) {
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }

    function validatePassword(pw, options) {
        var o = {
            lower: 0,
            upper: 0,
            alpha: 0,
            numeric: 0,
            special: 0,
            length: [0, Infinity],
            custom: [],
            badWords: [],
            badSequenceLength: 0,
            noQwertySequences: false,
            noSequential: false
        };

        for (var property in options) {
            o[property] = options[property];
        }

        var re = {
            lower: /[a-z]/g,
            upper: /[A-Z]/g,
            alpha: /[A-Z]/gi,
            numeric: /[0-9]/g,
            special: /[\W_]/g
        };

        if (pw.length < o.length[0] || pw.length > o.length[1]) {
            return false;
        }

        for (var rule in re) {
            if ((pw.match(re[rule]) || []).length < o[rule]) {
                return false;
            }
        }

        for (var i = 0; i < o.badWords.length; i++) {
            if (pw.toLowerCase().indexOf(o.badWords[i].toLowerCase()) > -1) {
                return false;
            }
        }

        if (o.noSequential && /([\S\s])\1/.test(pw)) {
            return false;
        }

        if (o.badSequenceLength) {
            var lower = "abcdefghijklmnopqrstuvwxyz",
                upper = lower.toUpperCase(),
                numbers = "0123456789",
                qwerty = "qwertyuiopasdfghjklzxcvbnm",
                start = o.badSequenceLength - 1,
                seq = "_" + pw.slice(0, start);

            for (i = start; i < pw.length; i++) {
                seq = seq.slice(1) + pw.charAt(i);
                if (lower.indexOf(seq) > -1 ||
                    upper.indexOf(seq) > -1 ||
                    numbers.indexOf(seq) > -1 ||
                    (o.noQwertySequences && qwerty.indexOf(seq) > -1)) {
                    return false;
                }
            }
        }

        return true;
    }

    function check_Password(password) {
        return validatePassword(password, {
            length: [8, 12],
            lower: 1,
            upper: 1,
            numeric: 1,
            special: 1,
            badWords: ["password", "qwerty", "12345"],
            badSequenceLength: 4
        });
    }

    function signUp() {
        // Validate email
        var email = $("#new_email").val();
        if (!validateEmail(email)) {
            $("#result").html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo xlt('Please provide a valid e-mail address to proceed'); ?>...</div>');
            return false;
        }

        // Validate password
        var password = $("#new_password").val();
        if (!check_Password(password)) {
            $("#result").html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo xlt('Passwords must be 8-12 characters long and include one capital letter, one lower case letter and one special character'); ?>...</div>');
            return false;
        }

        // Check password match
        if ($("#new_rpassword").val() !== password) {
            $("#result").html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo xlt('Passwords do not match'); ?>!</div>');
            return false;
        }

        // Check terms
        if (!$("#TERMS_yes").is(':checked')) {
            $("#result").html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo xlt('You must agree to the Terms & Conditions before signing up'); ?>...</div>');
            return false;
        }

        // Check BAA
        if (!$("#BusAgree_yes").is(':checked')) {
            $("#result").html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo xlt('You must agree to the HIPAA Business Associate Agreement'); ?>...</div>');
            return false;
        }

        // Check production confirmation
        if (!$("#Production_yes").is(':checked')) {
            $("#result").html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo xlt('Production verification confirmation is required'); ?>...</div>');
            return false;
        }

        // Check callback URL
        var callbackUrl = $("#callback_url").val();
        if (!callbackUrl || !callbackUrl.startsWith('https://')) {
            $("#result").html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo xlt('Callback URL is required and must use HTTPS'); ?>.</div>');
            return false;
        }

        // Submit registration
        $("#result").html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> <?php echo xlt("Registering your practice"); ?>...</div>');

        $.ajax({
            url: 'register_process.php',
            type: 'POST',
            data: {
                csrf_token_form: $('input[name="csrf_token_form"]').val(),
                email: email,
                password: password,
                callback_url: callbackUrl,
                production_confirm: '1'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $("#result").html('<div class="alert alert-success"><strong><i class="fa fa-check-circle"></i> <?php echo xlt("Registration Successful"); ?>!</strong><br>' +
                        '<?php echo xlt("Practice ID"); ?>: ' + response.practice_id + '<br>' +
                        '<?php echo xlt("Redirecting to settings"); ?>...</div>');
                    $("#setup_1").hide();

                    // Redirect to settings page after 2 seconds
                    setTimeout(function() {
                        window.location.href = 'settings.php';
                    }, 2000);
                } else {
                    $("#result").html('<div class="alert alert-danger"><strong><i class="fa fa-exclamation-circle"></i> <?php echo xlt("Registration Failed"); ?></strong><br>' +
                        response.error + '</div>');
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = '<?php echo xlt("An error occurred during registration"); ?>';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        errorMsg = response.error;
                        if (response.debug) {
                            errorMsg += '<br><small>' + response.debug + '</small>';
                        }
                    }
                } catch(e) {
                    errorMsg += '<br>Status: ' + status + '<br>Error: ' + error;
                    if (xhr.responseText) {
                        errorMsg += '<br><small>' + xhr.responseText.substring(0, 500) + '</small>';
                    }
                }
                $("#result").html('<div class="alert alert-danger"><strong><i class="fa fa-exclamation-circle"></i> <?php echo xlt("Error"); ?></strong><br>' + errorMsg + '</div>');
            }
        });
    }

    // Show/hide password help based on complexity
    $('#new_password').on('input focus', function() {
        var password = $(this).val();

        if (password.length === 0) {
            // No password typed yet, hide help
            $('#pwd_help').addClass('nodisplay');
            $('#pwd_check').addClass('nodisplay');
            return;
        }

        // Check if password meets complexity requirements
        if (check_Password(password)) {
            // Password is valid - hide help, show check
            $('#pwd_help').addClass('nodisplay');
            $('#pwd_check').removeClass('nodisplay');
        } else {
            // Password doesn't meet requirements - show help, hide check
            $('#pwd_help').removeClass('nodisplay');
            $('#pwd_check').addClass('nodisplay');
        }
    });

    $('#new_password').on('blur', function() {
        var password = $(this).val();
        // Keep showing check if valid, otherwise hide everything on blur
        if (!check_Password(password)) {
            $('#pwd_help').addClass('nodisplay');
            $('#pwd_check').addClass('nodisplay');
        }
    });

    // Show/hide repeat password help based on match
    $('#new_rpassword').on('input focus', function() {
        var password = $('#new_password').val();
        var repeatPassword = $(this).val();

        if (repeatPassword.length === 0) {
            // No repeat password typed yet, hide help
            $('#pwd_rhelp').addClass('nodisplay');
            $('#pwd_rcheck').addClass('nodisplay');
            return;
        }

        // Check if passwords match
        if (password === repeatPassword) {
            // Passwords match - hide help, show check
            $('#pwd_rhelp').addClass('nodisplay');
            $('#pwd_rcheck').removeClass('nodisplay');
        } else {
            // Passwords don't match - show help, hide check
            $('#pwd_rhelp').removeClass('nodisplay');
            $('#pwd_rcheck').addClass('nodisplay');
        }
    });

    $('#new_rpassword').on('blur', function() {
        var password = $('#new_password').val();
        var repeatPassword = $(this).val();
        // Keep showing check if match, otherwise hide everything on blur
        if (password !== repeatPassword) {
            $('#pwd_rhelp').addClass('nodisplay');
            $('#pwd_rcheck').addClass('nodisplay');
        }
    });

    // Toggle password visibility
    $('#togglePassword').on('click', function() {
        const passwordField = $('#new_password');
        const icon = $(this);

        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });

    $('#togglePasswordRepeat').on('click', function() {
        const passwordField = $('#new_rpassword');
        const icon = $(this);

        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });
    </script>
</body>
</html>
