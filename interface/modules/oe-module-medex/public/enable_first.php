<?php
/**
 * MedEx Module - Enable First Message
 * Shown when module is installed but not enabled
 */

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Core\Header;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('MedEx - Enable Module'); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .message-container {
            max-width: 500px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
        }

        .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        h2 {
            color: #333;
            margin: 0 0 15px 0;
            font-size: 24px;
        }

        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .steps {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            text-align: left;
            margin-bottom: 25px;
        }

        .step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .step:last-child {
            margin-bottom: 0;
        }

        .step-number {
            background: #0f4b8f;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
            margin-right: 12px;
        }

        .step-text {
            color: #333;
            font-size: 15px;
            line-height: 28px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn:hover {
            background: #5a6268;
            color: white;
        }
    </style>
</head>
<body>
    <div class="message-container">
        <div class="icon">⚙️</div>
        <h2><?php echo xlt('Module Not Enabled'); ?></h2>
        <p><?php echo xlt('MedEx is installed but not yet enabled. Please complete the following steps in Module Manager:'); ?></p>

        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text"><?php echo xlt('Click the "Enable" button in the Action column'); ?></div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text"><?php echo xlt('Click "Upgrade SQL" if it appears'); ?></div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text"><?php echo xlt('Click the gear icon again to configure MedEx'); ?></div>
            </div>
        </div>

        <button type="button" class="btn" onclick="window.parent.location.reload()">
            <i class="fa fa-arrow-left"></i> <?php echo xlt('Back to Module Manager'); ?>
        </button>
    </div>
</body>
</html>
