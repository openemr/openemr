<?php
/**
 * MedEx Module Cleanup Utility
 *
 * This script cleans up MedEx data from the writable documents directory.
 * Run this before uninstalling the module to remove all downloaded assets.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;

// Security: Only admins can run cleanup
if (!AclMain::aclCheckCore('admin', 'super')) {
    die('Access denied. Administrator privileges required.');
}

$siteId = $_SESSION['site_id'] ?? 'default';
$medexDir = $GLOBALS['OE_SITES_BASE'] . "/$siteId/documents/MedEx";

$cleaned = false;
$message = '';

// Handle cleanup request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_cleanup'])) {
    if (\OpenEMR\Common\Csrf\CsrfUtils::verifyCsrfToken($_POST['csrf_token'], 'default')) {
        try {
            // Remove the MedEx directory recursively
            if (is_dir($medexDir)) {
                $success = removeDirectory($medexDir);
                if ($success) {
                    $message = "Successfully cleaned up MedEx directory: $medexDir";
                    $cleaned = true;
                } else {
                    $message = "Error: Could not remove directory: $medexDir";
                }
            } else {
                $message = "Directory does not exist: $medexDir";
            }
        } catch (\Exception $e) {
            $message = "Error during cleanup: " . $e->getMessage();
        }
    } else {
        $message = "Invalid CSRF token";
    }
}

// Recursive directory removal function
function removeDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!removeDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }

    return rmdir($dir);
}

// Check current state
$dirExists = is_dir($medexDir);
$dirSize = 0;
$fileCount = 0;

if ($dirExists) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($medexDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $dirSize += $file->getSize();
            $fileCount++;
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('MedEx Cleanup Utility'); ?></title>
    <link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/public/themes/style_manila.css">
    <style>
        .cleanup-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .status.exists {
            background: #fff3cd;
            border: 1px solid #ffc107;
        }
        .status.cleaned {
            background: #d4edda;
            border: 1px solid #28a745;
        }
        .status.error {
            background: #f8d7da;
            border: 1px solid #dc3545;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-danger:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="cleanup-container">
        <h2><?php echo xlt('MedEx Module Cleanup'); ?></h2>

        <p><?php echo xlt('This utility removes downloaded assets and cached data from the MedEx module.'); ?></p>

        <?php if ($message): ?>
            <div class="status <?php echo $cleaned ? 'cleaned' : 'error'; ?>">
                <?php echo text($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($dirExists && !$cleaned): ?>
            <div class="status exists">
                <h3><?php echo xlt('MedEx Directory Found'); ?></h3>
                <p><strong><?php echo xlt('Location'); ?>:</strong> <?php echo text($medexDir); ?></p>
                <p><strong><?php echo xlt('Files'); ?>:</strong> <?php echo text($fileCount); ?></p>
                <p><strong><?php echo xlt('Total Size'); ?>:</strong> <?php echo text(number_format($dirSize / 1024, 2)); ?> KB</p>
            </div>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo attr(\OpenEMR\Common\Csrf\CsrfUtils::collectCsrfToken()); ?>">
                <input type="hidden" name="confirm_cleanup" value="1">
                <p>
                    <strong><?php echo xlt('Warning'); ?>:</strong>
                    <?php echo xlt('This will permanently delete the MedEx directory and all downloaded FullCalendar assets.'); ?>
                </p>
                <button type="submit" class="btn-danger" onclick="return confirm('<?php echo xla('Are you sure you want to delete the MedEx directory?'); ?>')">
                    <?php echo xlt('Delete MedEx Directory'); ?>
                </button>
            </form>
        <?php elseif (!$dirExists): ?>
            <div class="status cleaned">
                <p><?php echo xlt('No MedEx directory found. Nothing to clean up.'); ?></p>
            </div>
        <?php endif; ?>

        <p style="margin-top: 30px;">
            <a href="<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/admin/settings.php">
                ← <?php echo xlt('Back to Settings'); ?>
            </a>
        </p>
    </div>
</body>
</html>
