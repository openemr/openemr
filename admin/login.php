<?php

/**
 * Admin Login Controller
 *
 * Front controller for multi-site administration authentication.
 * Authenticates users against the default site database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Prevent UI redressing
header("X-Frame-Options: DENY");
header("Content-Security-Policy: frame-ancestors 'none'");

use OpenEMR\Admin\AdminAuthService;
use OpenEMR\Common\Twig\TwigContainer;

// Set session to allow write for authentication
$ignoreAuth = true;
$sessionAllowWrite = true;

// Include globals to initialize the system
require_once(dirname(__FILE__) . "/../interface/globals.php");

// Ensure we're using the default site
$_SESSION['site_id'] = 'default';

// Initialize authentication service
$authService = new AdminAuthService();

// Check if already authenticated
if ($authService->isAuthenticated() && $authService->checkSessionTimeout()) {
    // Already logged in, redirect to admin dashboard
    header('Location: index.php');
    exit;
}

// Handle login submission
$loginFail = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $username = $_POST['authUser'] ?? '';
    $password = $_POST['clearPass'] ?? '';

    // Attempt authentication
    $result = $authService->authenticate($username, $password);

    if ($result['success']) {
        // Initialize session
        $authService->initializeSession($result['user_id'], $result['username']);
        
        // Clear password from memory
        if (!empty($_POST['clearPass'])) {
            if (function_exists('sodium_memzero')) {
                sodium_memzero($_POST['clearPass']);
            } else {
                $_POST['clearPass'] = '';
            }
        }
        
        // Redirect to admin dashboard
        header('Location: index.php');
        exit;
    } else {
        $loginFail = true;
        $errorMessage = $result['message'];
        
        // Clear password from memory on failure
        if (!empty($_POST['clearPass'])) {
            if (function_exists('sodium_memzero')) {
                sodium_memzero($_POST['clearPass']);
            } else {
                $_POST['clearPass'] = '';
            }
        }
    }
}

// Get version from version.php
require_once(dirname(__FILE__) . "/../version.php");

// Prepare template variables
$viewArgs = [
    'loginFail' => $loginFail,
    'errorMessage' => $errorMessage,
    'version' => $v_realpatch ?? 'Unknown',
];

// Render login page with Twig
try {
    $twig = new TwigContainer(null, $GLOBALS['kernel']);
    echo $twig->getTwig()->render('admin/login.html.twig', $viewArgs);
} catch (\Exception $e) {
    error_log("Admin login Twig rendering failed: " . $e->getMessage());
    // Fallback to simple error page
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Login Error</title>
    </head>
    <body>
        <h1>Login Error</h1>
        <p>Unable to load the login page. Please check the system logs.</p>
    </body>
    </html>
    <?php
}
