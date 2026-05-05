<?php
/**
 * Fix MedEx URL Configuration
 * Updates database to point to branded MedEx host
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    die("Access denied. Admin access required.");
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix_url'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '', 'default')) {
        $error = 'Invalid security token';
    } else {
        try {
            // Update globals table
            sqlStatement("UPDATE globals SET gl_value = ? WHERE gl_name = 'medex_bank_url'", ['https://medexbank.com/cart/upload']);
            
            // Update medex_prefs table
            sqlStatement("UPDATE medex_prefs SET ME_server_url = ? WHERE ME_server_url LIKE '%localhost%' OR ME_server_url LIKE '%orb.local%'", ['https://medexbank.com/cart/upload']);
            
            $message = 'MedEx URL updated successfully to https://medexbank.com/cart/upload';
            
            // Clear any cached configuration
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get current values
$currentUrl = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'medex_bank_url'")['gl_value'] ?? 'Not set';
$medexPrefs = sqlQuery("SELECT ME_server_url FROM medex_prefs WHERE ME_server_url IS NOT NULL LIMIT 1")['ME_server_url'] ?? 'Not set';

$csrfToken = CsrfUtils::collectCsrfToken();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix MedEx URL</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        .info-box { background: #e3f2fd; border: 1px solid #90caf9; padding: 15px; border-radius: 6px; margin: 20px 0; }
        .alert { padding: 12px; border-radius: 6px; margin: 20px 0; }
        .alert-error { background: #fee; color: #c33; border: 1px solid #fcc; }
        .alert-success { background: #efe; color: #3c3; border: 1px solid #cfc; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        code { background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix MedEx URL Configuration</h1>
        
        <div class="info-box">
            <strong>Current Configuration:</strong><br>
            globals.medex_bank_url: <code><?php echo htmlspecialchars($currentUrl); ?></code><br>
            medex_prefs.ME_server_url: <code><?php echo htmlspecialchars($medexPrefs); ?></code>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert alert-success">
                <strong>Success:</strong> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token_form" value="<?php echo $csrfToken; ?>">
            <p>This will update the MedEx URL from:</p>
            <ul>
                <li><code><?php echo htmlspecialchars($currentUrl); ?></code></li>
            </ul>
            <p>To:</p>
            <ul>
                <li><code>https://medexbank.com/cart/upload</code></li>
            </ul>
            
            <button type="submit" name="fix_url" class="btn">Update MedEx URL</button>
        </form>
        
        <p><a href="index.php">← Back to MedEx Dashboard</a></p>
    </div>
</body>
</html>
