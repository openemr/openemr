<?php

/**
 * MedEx to OpenEMR Portal Redirect
 * 
 * Validates bearer token and creates portal session, then redirects to OpenEMR portal messaging
 * Used when user clicks secure chat link with &use_portal=1 parameter
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ray Magauran
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$ignoreAuth = true;
require_once(__DIR__ . "/../../../../globals.php");
require_once($GLOBALS['srcdir'] . "/patient.inc.php");

use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;

// Get token from URL
$token = $_GET['token'] ?? '';
$lastName = $_GET['last_name'] ?? ''; // For verification if needed

if (empty($token)) {
    http_response_code(400);
    echo "Missing token parameter";
    exit;
}

// Validate token exists in medex_secure_chat_tokens
$tokenData = sqlQuery(
    "SELECT mst.pid, mst.is_provider, mst.user_initials, mst.expires_at, 
            pd.fname, pd.lname, pd.allow_patient_portal 
     FROM medex_secure_chat_tokens mst
     LEFT JOIN patient_data pd ON pd.pid = mst.pid
     WHERE mst.token = ?
     LIMIT 1",
    [$token]
);

if (!$tokenData) {
    http_response_code(404);
    echo "Invalid or expired token";
    exit;
}

// Check expiration
if (!empty($tokenData['expires_at']) && strtotime($tokenData['expires_at']) < time()) {
    http_response_code(403);
    echo "Token has expired";
    exit;
}

// Provider tokens bypass verification, patient tokens need last name verification
$isProvider = (bool)($tokenData['is_provider'] ?? 0);
$pid = $tokenData['pid'];

if (!$isProvider) {
    // Patient token - verify last name if not already verified in session
    session_start();
    $sessionKey = 'medex_portal_verified_' . $token;
    
    if (!isset($_SESSION[$sessionKey])) {
        // Need verification
        if (empty($lastName)) {
            // Show verification form
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>Verify Identity</title>
                <style>
                    body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; padding: 20px; }
                    .form-group { margin-bottom: 15px; }
                    label { display: block; margin-bottom: 5px; font-weight: bold; }
                    input[type="text"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
                    button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
                    button:hover { background: #0056b3; }
                    .error { color: red; margin-bottom: 15px; }
                </style>
            </head>
            <body>
                <h2>Verify Your Identity</h2>
                <p>Please enter your last name to access secure messaging.</p>
                <form method="get">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="form-group">
                        <label>Last Name:</label>
                        <input type="text" name="last_name" required autocomplete="family-name">
                    </div>
                    <button type="submit">Verify</button>
                </form>
            </body>
            </html>
            <?php
            exit;
        }
        
        // Verify last name
        $expectedLast = strtolower(trim($tokenData['lname'] ?? ''));
        $inputLast = strtolower(trim($lastName));
        
        if ($expectedLast !== $inputLast) {
            http_response_code(403);
            echo "Verification failed. Please check your last name.";
            exit;
        }
        
        // Store verification in session
        $_SESSION[$sessionKey] = true;
    }
}

// Check if patient portal is enabled for this patient
if (!$isProvider && $tokenData['allow_patient_portal'] !== 'YES') {
    http_response_code(403);
    echo "Patient portal access not enabled for this account. Please contact your provider.";
    exit;
}

// Create portal session
$session = SessionWrapperFactory::getInstance()->getWrapper();

// Set portal session variables
$_SESSION['pid'] = $pid;
$_SESSION['patient_portal_onsite_two'] = 1;
$_SESSION['portal_username'] = (string)$pid; // Portal username is typically the PID

// For provider tokens, set different session type
if ($isProvider) {
    $_SESSION['medex_provider_view'] = 1;
    $_SESSION['provider_initials'] = $tokenData['user_initials'] ?? '';
}

// Write session
session_write_close();

// Redirect to OpenEMR portal messaging
$portalUrl = $GLOBALS['web_root'] . '/portal/messaging/messages.php';
header('Location: ' . $portalUrl);
exit;
