<?php
/**
 * MedEx Module - Manual Configuration
 *
 * For manually entering API credentials when automatic registration fails
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

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_credentials'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '', 'default')) {
        $error = 'Invalid security token';
    } else {
        $practiceId = trim($_POST['practice_id'] ?? '');
        $apiKey = trim($_POST['api_key'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($practiceId) || empty($apiKey)) {
            $error = 'Practice ID and API Key are required';
        } else {
            try {
                // Save to globals
                sqlStatement("REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_practice_id', 0, ?)", [$practiceId]);
                sqlStatement("REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_api_key', 0, ?)", [$apiKey]);
                sqlStatement("REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_enable', 0, '1')");

                // Update medex_prefs
                if (!empty($email)) {
                    sqlStatement("UPDATE medex_prefs SET ME_username = ?, MedEx_id = ?, MedEx_lastupdated = NOW() WHERE 1", [
                        $email,
                        $practiceId
                    ]);
                }

                $success = 'Credentials saved successfully! Redirecting to dashboard...';
                header("Refresh: 2; url=index.php");
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Get existing values
$existingPracticeId = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'medex_practice_id'")['gl_value'] ?? '';
$existingApiKey = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'medex_api_key'")['gl_value'] ?? '';
$existingEmail = sqlQuery("SELECT ME_username FROM medex_prefs WHERE ME_username IS NOT NULL LIMIT 1")['ME_username'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt("Manual MedEx Configuration"); ?></title>
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
            max-width: 600px;
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
            line-height: 1.6;
        }
        .info-box {
            background: #e3f2fd;
            border: 1px solid #90caf9;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 25px;
            font-size: 13px;
            line-height: 1.6;
        }
        .info-box strong {
            display: block;
            margin-bottom: 8px;
            color: #1976d2;
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
        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
            font-family: monospace;
        }
        textarea {
            min-height: 80px;
            resize: vertical;
        }
        input:focus, textarea:focus {
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
        .help-text {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fa fa-wrench"></i> <?php echo xlt('Manual MedEx Configuration'); ?></h1>
        <p class="subtitle"><?php echo xlt('Enter your MedEx API credentials directly. Use this if automatic registration is failing.'); ?></p>

        <div class="info-box">
            <strong><i class="fa fa-info-circle"></i> <?php echo xlt('Where to find these credentials:'); ?></strong>
            <?php echo xlt('Log into your MedEx admin portal at'); ?> <code>https://medexbank.com</code> <?php echo xlt('and navigate to Settings > API Credentials to find your Practice ID and API Key.'); ?>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fa fa-exclamation-circle"></i> <?php echo text($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i> <?php echo text($success); ?>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">

                <div class="form-group">
                    <label><?php echo xlt('Practice ID'); ?> *</label>
                    <input type="text" name="practice_id" value="<?php echo attr($existingPracticeId); ?>" required placeholder="<?php echo xla('e.g., 10421'); ?>">
                    <div class="help-text"><?php echo xlt('Your unique practice identifier from MedEx'); ?></div>
                </div>

                <div class="form-group">
                    <label><?php echo xlt('API Key'); ?> *</label>
                    <textarea name="api_key" required placeholder="<?php echo xla('Paste your encrypted API key here'); ?>"><?php echo attr($existingApiKey); ?></textarea>
                    <div class="help-text"><?php echo xlt('Long encrypted string from your MedEx account settings'); ?></div>
                </div>

                <div class="form-group">
                    <label><?php echo xlt('Email Address'); ?></label>
                    <input type="email" name="email" value="<?php echo attr($existingEmail); ?>" placeholder="<?php echo xla('Your MedEx account email (optional)'); ?>">
                    <div class="help-text"><?php echo xlt('Email associated with this MedEx account'); ?></div>
                </div>

                <button type="submit" name="save_credentials" class="btn">
                    <i class="fa fa-save"></i> <?php echo xlt('Save Credentials'); ?>
                </button>
            </form>

            <a href="index.php" class="back-link">
                <i class="fa fa-arrow-left"></i> <?php echo xlt('Back to Dashboard'); ?>
            </a>
        <?php endif; ?>
    </div>
</body>
</html>
