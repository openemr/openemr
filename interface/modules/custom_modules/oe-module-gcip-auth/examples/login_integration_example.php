<?php

/**
 * Example GCIP Login Integration
 * 
 * <!-- AI-Generated Content Start -->
 * This example demonstrates how to integrate the GCIP authentication
 * module into an existing OpenEMR login form, showing the proper
 * usage of the LoginIntegrationHelper class.
 * <!-- AI-Generated Content End -->
 *
 * @package   OpenEMR GCIP Authentication Module Examples
 * @link      http://www.open-emr.org
 * @author    OpenEMR Development Team
 * @copyright Copyright (c) 2024 OpenEMR Development Team
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// This is an example file showing how to integrate GCIP into login forms

require_once dirname(__FILE__, 5) . '/globals.php';

use OpenEMR\Core\ModulesClassLoader;
use OpenEMR\Modules\GcipAuth\Helpers\LoginIntegrationHelper;

// Load the GCIP module classes - AI-Generated
$classLoader = new ModulesClassLoader($GLOBALS['fileroot']);
$classLoader->registerNamespaceIfNotExists("OpenEMR\\Modules\\GcipAuth\\", dirname(__DIR__) . '/src');

// Initialize the integration helper - AI-Generated
$gcipHelper = new LoginIntegrationHelper();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Example GCIP Login Integration</title>
    <link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/css/bootstrap.min.css">
    <?php echo $gcipHelper->getGcipLoginStyles(); ?>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>OpenEMR Login Example</h4>
                    </div>
                    <div class="card-body">
                        
                        <?php 
                        // Display any GCIP authentication errors - AI-Generated
                        echo $gcipHelper->handleCallbackError(); 
                        ?>
                        
                        <!-- Standard OpenEMR Login Form - AI-Generated -->
                        <form method="post" action="<?php echo $GLOBALS['webroot']; ?>/interface/login/login.php">
                            <div class="form-group">
                                <label for="username">Username:</label>
                                <input type="text" class="form-control" id="username" name="authUser" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" class="form-control" id="password" name="clearPass" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Login</button>
                        </form>
                        
                        <?php 
                        // Add GCIP authentication option - AI-Generated
                        echo $gcipHelper->getGcipLoginButton(); 
                        ?>
                        
                    </div>
                </div>
                
                <!-- GCIP Module Information - AI-Generated -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5>GCIP Authentication Status</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($gcipHelper->shouldDisplayGcipLogin()): ?>
                            <div class="alert alert-success">
                                <i class="fa fa-check-circle"></i>
                                GCIP authentication is enabled and configured.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle"></i>
                                GCIP authentication is not available. Please check module configuration.
                            </div>
                        <?php endif; ?>
                        
                        <h6>Module Features:</h6>
                        <ul>
                            <li>Google Cloud Identity Platform integration</li>
                            <li>Single Sign-On with Google Workspace</li>
                            <li>Secure OAuth2 authentication flow</li>
                            <li>Encrypted credential storage</li>
                            <li>Comprehensive audit logging</li>
                            <li>Auto-user creation (optional)</li>
                            <li>Domain restriction support</li>
                        </ul>
                        
                        <?php if (isset($_SESSION['gcip_authenticated']) && $_SESSION['gcip_authenticated']): ?>
                            <div class="alert alert-info">
                                <strong>Current Session:</strong> Authenticated via GCIP<br>
                                <strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['gcip_email'] ?? 'N/A'); ?><br>
                                <strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['gcip_name'] ?? 'N/A'); ?>
                                <?php echo $gcipHelper->getGcipStatusIndicator(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery.min.js"></script>
    <script src="<?php echo $GLOBALS['webroot']; ?>/library/js/bootstrap.min.js"></script>
</body>
</html>