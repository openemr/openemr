<?php
/**
 * MedEx Module - Help Page
 * Provides guidance to the user on how to use the module
 */

require_once(__DIR__ . "/../../../globals.php");

use OpenEMR\Core\Header;

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt("MedEx Help"); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 20px;
            background: #f8fafc;
            line-height: 1.6;
            color: #2c3e50;
        }
        .help-container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        h1 {
            color: #0f4b8f;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
            font-size: 1.75rem;
        }
        .step {
            margin-bottom: 20px;
        }
        .step h2 {
            font-size: 1.25rem;
            color: #0f4b8f;
            display: flex;
            align-items: center;
        }
        .step h2 i {
            margin-right: 15px;
            background: #f0f7ff;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        .highlight {
            background: #fffbeb;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
            color: #92400e;
        }
    </style>
    <script>
        function closeAndOpenStatus() {
            // If we're in a popup, close it and trigger the gear icon click in the parent
            if (window.opener && !window.opener.closed) {
                // Find the gear icon/configure link for MedEx module in the parent window
                try {
                    // The gear icon is in a configure() call - we need to trigger that
                    // Look for the MedEx module row and click its gear icon
                    const parentDoc = window.opener.document;
                    const configLinks = parentDoc.querySelectorAll('a[onclick*="configure"]');

                    for (let link of configLinks) {
                        const onclick = link.getAttribute('onclick');
                        // Find the one for our module - check if it's in the MedEx row
                        const row = link.closest('tr');
                        if (row && row.textContent.includes('MedEx Communication Manager')) {
                            window.close(); // Close help popup first
                            link.click(); // Then click the gear icon
                            return;
                        }
                    }

                    // Fallback: just close and let user click manually
                    alert('<?php echo xlt("Please click the gear icon next to MedEx Communication Manager in the Module Manager."); ?>');
                    window.close();
                } catch (e) {
                    console.error('Could not access parent window:', e);
                    window.close();
                }
            } else {
                // Not in a popup, just redirect to status page
                if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
                    top.restoreSession();
                }
                window.location.href = 'public/status.php';
            }
        }
    </script>
</head>
<body>
    <div class="help-container">
        <h1><?php echo xlt("Getting Started with MedEx"); ?></h1>

        <div class="step">
            <h2><i class="fa fa-cogs"></i> 1. <?php echo xlt("Configuration"); ?></h2>
            <p><?php echo xlt("To begin using the MedEx Communication Platform, you must first configure your connection. Click the"); ?>
            <a href="javascript:void(0)" onclick="closeAndOpenStatus()" class="highlight" style="cursor: pointer; text-decoration: none;">
                <i class="fa fa-gear"></i> <?php echo xlt("Gear Icon"); ?>
            </a>
            <?php echo xlt("next to the module in the Module Manager to access the MedEx Dashboard."); ?></p>
        </div>

        <div class="step">
            <h2><i class="fa fa-user-plus"></i> 2. <?php echo xlt("Registration"); ?></h2>
            <p><?php echo xlt("If you are a new customer, you will be guided through a splash page and an onboarding wizard to create your account and select your communication services."); ?></p>
        </div>

        <div class="step">
            <h2><i class="fa fa-check-circle"></i> 3. <?php echo xlt("Activation"); ?></h2>
            <p><?php echo xlt("Once registered and configured, your subscriptions will be verified. After verification, MedEx will automatically integrate with your Flow Board, Recall Board, and Message Center."); ?></p>
        </div>

        <p style="margin-top: 40px; font-size: 0.9rem; color: #64748b; text-align: center;">
            &copy; 2018-2026 MedEx Bank. <?php echo xlt("All Rights Reserved."); ?>
        </p>
    </div>
</body>
</html>
