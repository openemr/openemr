<?php
/**
 * MedEx Module - Dashboard
 *
 * Embeds the MedEx SaaS dashboard showing:
 * - Account overview
 * - Recent activity
 * - Quick links to features
 * - Service status
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2025 MedEx
 * @license   Proprietary - All Rights Reserved
 */

require_once(__DIR__ . '/../../../../globals.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;

// Verify user is authenticated
if (!isset($_SESSION['authUserID'])) {
    die('Access denied. Please log in to OpenEMR.');
}

// Load MedEx API
require_once(__DIR__ . '/../src/MedExAPI.php');
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

// Check if configured
if (!$api->isConfigured()) {
    die('MedEx not configured. Please configure MedEx in <a href="../admin/settings.php">Settings</a>.');
}

// Get dashboard URL with SSO
$dashboardUrl = $api->getSaaSUrl('dashboard');

if (!$dashboardUrl) {
    die('Unable to generate dashboard URL. Please check your MedEx configuration.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt('MedEx Dashboard'); ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        #medex-dashboard-frame {
            width: 100%;
            height: 100vh;
            border: none;
        }
        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="loading" id="loading">
        <i class="fa fa-spinner fa-spin fa-3x"></i>
        <p><?php echo xlt('Loading MedEx Dashboard'); ?>...</p>
    </div>
    <iframe
        id="medex-dashboard-frame"
        src="<?php echo attr($dashboardUrl); ?>"
        allow="payment *; clipboard-read *; clipboard-write *"
        onload="document.getElementById('loading').style.display='none';"
    ></iframe>
</body>
</html>
