<?php
/**
 * MedEx Module - Manual Credential Entry
 *
 * For development or when you already have MedEx credentials
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
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("MedEx Manual Setup")]);
    exit;
}

// Handle form submission
if ($_POST && CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'default')) {
    $practice_id = trim($_POST['practice_id'] ?? '');
    $api_key = trim($_POST['api_key'] ?? '');
    $server_url = trim($_POST['server_url'] ?? 'http://localhost/cart/upload');

    if (!empty($practice_id) && !empty($api_key)) {
        // Save to globals
        sqlStatement("REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_practice_id', 0, ?)", [$practice_id]);
        sqlStatement("REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_api_key', 0, ?)", [$api_key]);
        sqlStatement("REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_server_url', 0, ?)", [$server_url]);
        sqlStatement("REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_enable', 0, '1')", []);

        // Save to legacy medex_prefs
        sqlStatement(
            "REPLACE INTO medex_prefs (MedEx_id, ME_api_key) VALUES (?, ?)",
            [$practice_id, $api_key]
        );

        $success = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt("MedEx Manual Setup"); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        body {
            background: #f5f5f5;
        }
        .container {
            max-width: 700px;
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }
        .help-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        .btn-primary {
            background: #0f4b8f;
            color: white;
        }
        .btn-primary:hover {
            background: #0a3460;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo xlt("Manual MedEx Credentials"); ?></h2>

        <?php if (isset($success) && $success): ?>
            <div class="alert alert-success">
                <strong><i class="fa fa-check-circle"></i> <?php echo xlt("Credentials Saved"); ?>!</strong><br>
                <?php echo xlt("Your MedEx configuration has been saved successfully"); ?>.
                <br><br>
                <a href="settings.php" class="btn btn-primary"><?php echo xlt("Go to Settings"); ?></a>
            </div>
        <?php else: ?>

            <div class="alert alert-info">
                <strong><i class="fa fa-info-circle"></i> <?php echo xlt("Manual Configuration"); ?></strong><br>
                <?php echo xlt("Use this form if you already have MedEx credentials from your database"); ?>.
            </div>

            <form method="post">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

                <div class="form-group">
                    <label for="practice_id"><?php echo xlt("Practice ID"); ?> *</label>
                    <input type="text"
                           name="practice_id"
                           id="practice_id"
                           class="form-control"
                           placeholder="59"
                           required />
                    <div class="help-text">
                        <?php echo xlt("Your customer_id from"); ?> <code>oc_customer.customer_id</code>
                    </div>
                </div>

                <div class="form-group">
                    <label for="api_key"><?php echo xlt("API Key"); ?> *</label>
                    <textarea name="api_key"
                              id="api_key"
                              class="form-control"
                              rows="4"
                              placeholder="Your long API key from oc_customer.api_key"
                              required></textarea>
                    <div class="help-text">
                        <?php echo xlt("Your API key from"); ?> <code>oc_customer.api_key</code>
                    </div>
                </div>

                <div class="form-group">
                    <label for="server_url"><?php echo xlt("MedEx Server URL"); ?></label>
                    <input type="text"
                           name="server_url"
                           id="server_url"
                           class="form-control"
                           value="http://localhost/cart/upload"
                           placeholder="http://localhost/cart/upload" />
                    <div class="help-text">
                        <?php echo xlt("URL to your MedEx server (without trailing slash)"); ?>
                    </div>
                </div>

                <div class="form-group" style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> <?php echo xlt("Save Credentials"); ?>
                    </button>
                </div>
            </form>

            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                <p style="color: #6c757d; margin-bottom: 10px;">
                    <?php echo xlt("To get these values from your MedEx database"); ?>:
                </p>
                <pre style="background: #f4f4f4; padding: 15px; text-align: left; border-radius: 4px; font-size: 12px;">SELECT customer_id, api_key
FROM oc_customer
WHERE customer_id = 59;</pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
