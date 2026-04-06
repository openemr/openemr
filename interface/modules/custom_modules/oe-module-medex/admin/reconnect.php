<?php
/**
 * MedEx Module - Reconnect with Existing Account
 *
 * For when credentials are cleared but email is already registered
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo "Access denied";
    exit;
}

// Load MedEx API
require_once(__DIR__ . '/../src/MedExAPI.php');
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

$error = '';
$success = '';

// Get existing email from medex_prefs
$prefs = sqlQuery("SELECT ME_username FROM medex_prefs WHERE ME_username IS NOT NULL LIMIT 1");
$existingEmail = $prefs['ME_username'] ?? '';

// Build externally reachable OpenEMR base URL for reconnect payload.
$forwardedProto = trim(explode(',', (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0] ?? '');
$scheme = $forwardedProto !== ''
    ? $forwardedProto
    : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
$forwardedHost = trim(explode(',', (string)($_SERVER['HTTP_X_FORWARDED_HOST'] ?? ''))[0] ?? '');
$host = $forwardedHost !== ''
    ? $forwardedHost
    : ($_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost'));
$webroot = rtrim((string)($GLOBALS['webroot'] ?? ''), '/');
$openemrBaseUrl = $scheme . '://' . $host . $webroot;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reconnect'])) {
    error_log('[MedEx Reconnect] Form submitted');
    
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '', 'default')) {
        $error = 'Invalid security token';
        error_log('[MedEx Reconnect] CSRF token invalid');
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        error_log('[MedEx Reconnect] Email: ' . $email . ', Password length: ' . strlen($password));

        if (empty($email) || empty($password)) {
            $error = 'Email and password are required';
            error_log('[MedEx Reconnect] Missing email or password');
        } else {
            // Get facility info for registration data
            $facility = sqlQuery("SELECT name, phone, street, city, state, postal_code FROM facility WHERE primary_business_entity = 1 ORDER BY id LIMIT 1");
            if (!$facility) {
                $facility = sqlQuery("SELECT name, phone, street, city, state, postal_code FROM facility ORDER BY id LIMIT 1");
            }

            $practice_name = $facility['name'] ?? $GLOBALS['openemr_name'] ?? 'OpenEMR Practice';
            $practice_phone = $facility['phone'] ?? '';
            $practice_address = trim(
                ($facility['street'] ?? '') . "\n" .
                ($facility['city'] ?? '') . ', ' . ($facility['state'] ?? '') . ' ' . ($facility['postal_code'] ?? '')
            );

            // Try to register/reconnect via MedEx API
            try {
                error_log('[MedEx Reconnect] Building data payload...');
                $data = [
                    'email' => $email,
                    'password' => $password,
                    'practice_name' => $practice_name,
                    'phone' => $practice_phone,
                    'address' => $practice_address,
                    'callback_url' => $openemrBaseUrl,
                    'website_url' => $openemrBaseUrl,
                    'ehr' => 'OpenEMR',
                    'ehr_version' => ($GLOBALS['v_major'] ?? '0') . '.' . ($GLOBALS['v_minor'] ?? '0') . '.' . ($GLOBALS['v_patch'] ?? '0')
                ];

                error_log('[MedEx Reconnect] Calling API register()...');
                $response = $api->register($data);
                error_log('[MedEx Reconnect] API response: ' . json_encode($response));

                if (!empty($response['success'])) {
                    $success = 'Successfully reconnected to MedEx!';
                    
                    error_log('[MedEx Reconnect] Success for ' . $email);

                    // Note: Skipping initial sync during reconnection
                    // It will happen automatically on first API call
                    // require_once(__DIR__ . '/../src/Services/PracticeService.php');
                    // $practiceService = new \OpenEMR\Modules\MedEx\Services\PracticeService($api);
                    // $practiceService->performInitialSync();

                    error_log('[MedEx Reconnect] Setting redirect header...');
                    // Redirect to dashboard after 2 seconds
                    header("Refresh: 2; url=index.php?site=" . urlencode((string)($_GET['site'] ?? 'default')));
                    error_log('[MedEx Reconnect] Header set, continuing to render success page');
                } else {
                    $errorMsg = $response['error'] ?? 'Reconnection failed. Please check your credentials.';
                    error_log('[MedEx Reconnect] Failed for ' . $email . ': ' . $errorMsg);
                    $error = $errorMsg;
                }
            } catch (Exception $e) {
                $errorMsg = 'Connection error: ' . $e->getMessage();
                $error = $errorMsg;
                error_log('[MedEx Reconnect] Exception: ' . $errorMsg);
                error_log('[MedEx Reconnect] Stack trace: ' . $e->getTraceAsString());
            } catch (Throwable $e) {
                $errorMsg = 'Fatal error: ' . $e->getMessage();
                $error = $errorMsg;
                error_log('[MedEx Reconnect] Fatal error: ' . $errorMsg);
                error_log('[MedEx Reconnect] Stack trace: ' . $e->getTraceAsString());
            }
        }
    }
}

error_log('[MedEx Reconnect] Finished processing, rendering page. Success: ' . ($success ? 'true' : 'false') . ', Error: ' . ($error ? 'true' : 'false'));
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt("Reconnect to MedEx"); ?></title>
    <?php
    use OpenEMR\Core\Header;
    Header::setupHeader(['fontawesome']);
    ?>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 40px 20px;
            margin: 0;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fa fa-refresh"></i> <?php echo xlt('Reconnect to MedEx'); ?></h1>
        <p class="subtitle"><?php echo xlt('Enter your MedEx credentials to reconnect your account'); ?></p>
        
        <div class="alert alert-info" style="background: #e3f2fd; border: 1px solid #2196f3; padding: 15px; margin-bottom: 20px; border-radius: 6px;">
            <strong><i class="fa fa-info-circle"></i> <?php echo xlt('About Reconnecting'); ?>:</strong><br>
            <?php echo xlt('This will verify your credentials with the MedEx server and restore your connection. Your existing subscriptions and settings will be preserved.'); ?>
            <?php if ($existingEmail): ?>
                <br><br>
                <strong><?php echo xlt('Registered Email'); ?>:</strong> <?php echo text($existingEmail); ?>
            <?php endif; ?>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fa fa-exclamation-circle"></i> 
                <strong><?php echo xlt('Connection Failed'); ?></strong><br>
                <?php echo text($error); ?>
                <br><br>
                <small><strong><?php echo xlt('Tips'); ?>:</strong></small>
                <ul style="margin: 10px 0 0 20px; font-size: 12px;">
                    <li><?php echo xlt('Verify you are using the correct MedEx password (not your OpenEMR password)'); ?></li>
                    <li><?php echo xlt('Check that the email address matches your MedEx account'); ?></li>
                    <li><?php echo xlt('Contact support@medexbank.com if you need password assistance'); ?></li>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i> <?php echo text($success); ?>
                <br><small><?php echo xlt('Redirecting to dashboard...'); ?></small>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">

                <div class="form-group">
                    <label><?php echo xlt('Email Address'); ?></label>
                    <input type="email" name="email" value="<?php echo attr($existingEmail); ?>" required>
                </div>

                <div class="form-group">
                    <label><?php echo xlt('Password'); ?></label>
                    <input type="password" name="password" required placeholder="<?php echo xla('Enter your MedEx password'); ?>">
                </div>

                <button type="submit" name="reconnect" class="btn">
                    <i class="fa fa-sign-in"></i> <?php echo xlt('Reconnect'); ?>
                </button>
            </form>

            <a href="index.php?site=<?php echo urlencode((string)($_GET['site'] ?? 'default')); ?>" class="back-link">
                <i class="fa fa-arrow-left"></i> <?php echo xlt('Back to Dashboard'); ?>
            </a>
        <?php endif; ?>
    </div>
</body>
</html>
