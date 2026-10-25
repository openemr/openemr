<?php
/**
 * Installation script for General Dermatology module
 * 
 * This script checks for required dependencies and sets up the module.
 */

// Include OpenEMR globals
require_once(__DIR__ . "/../../globals.php");

// Define the module name constant for path construction
define('MODULE_NAME', 'general_dermatology');

// Check if Composer is installed
$composerInstalled = shell_exec('which composer') !== null;

// Check if vendor directory exists
$vendorDirExists = is_dir(__DIR__ . '/vendor');

// Check if mPDF is installed
$mpdfInstalled = file_exists(__DIR__ . '/vendor/mpdf/mpdf/src/Mpdf.php');

// Check if templates directory exists
$templatesDirExists = is_dir(__DIR__ . '/templates');

// Check if image directory exists
$imagesDirExists = is_dir(__DIR__ . '/images');

// Check if autoloader exists
$autoloaderExists = file_exists(__DIR__ . '/autoload.php');

// Check if src directory structure exists
$srcDirExists = is_dir(__DIR__ . '/src');
$modelsDirExists = is_dir(__DIR__ . '/src/Models');
$servicesDirExists = is_dir(__DIR__ . '/src/Services');
$utilsDirExists = is_dir(__DIR__ . '/src/Utils');

// Check if config directory exists
$configDirExists = is_dir(__DIR__ . '/config');

// Check if tmp directory exists
$tmpDirExists = is_dir(__DIR__ . '/tmp');

// Create directories if they don't exist
if (!$templatesDirExists) {
    mkdir(__DIR__ . '/templates', 0755, true);
}

if (!$imagesDirExists) {
    mkdir(__DIR__ . '/images', 0755, true);
}

if (!$configDirExists) {
    mkdir(__DIR__ . '/config', 0755, true);
}

if (!$tmpDirExists) {
    mkdir(__DIR__ . '/tmp', 0777, true); // Higher permissions for temp directory
} else {
    // Update permissions on existing tmp directory
    chmod(__DIR__ . '/tmp', 0777);
}

if (!$srcDirExists) {
    mkdir(__DIR__ . '/src', 0755, true);
}

if (!$modelsDirExists) {
    mkdir(__DIR__ . '/src/Models', 0755, true);
}

if (!$servicesDirExists) {
    mkdir(__DIR__ . '/src/Services', 0755, true);
}

if (!$utilsDirExists) {
    mkdir(__DIR__ . '/src/Utils', 0755, true);
}

// Display status
?>
<!DOCTYPE html>
<html>
<head>
    <title>General Dermatology Module Installation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #333;
        }
        .status {
            margin-bottom: 20px;
        }
        .status-item {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 4px;
        }
        .status-ok {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .status-warning {
            background-color: #fcf8e3;
            color: #8a6d3b;
        }
        .status-error {
            background-color: #f2dede;
            color: #a94442;
        }
        .instructions {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
        code {
            background-color: #eee;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <h1>General Dermatology Module Installation</h1>
    
    <div class="status">
        <h2>Installation Status</h2>
        
        <div class="status-item <?php echo $composerInstalled ? 'status-ok' : 'status-error'; ?>">
            <strong>Composer:</strong> <?php echo $composerInstalled ? 'Installed' : 'Not installed'; ?>
        </div>
        
        <div class="status-item <?php echo $vendorDirExists ? 'status-ok' : 'status-warning'; ?>">
            <strong>Vendor Directory:</strong> <?php echo $vendorDirExists ? 'Exists' : 'Does not exist'; ?>
        </div>
        
        <div class="status-item <?php echo $mpdfInstalled ? 'status-ok' : 'status-warning'; ?>">
            <strong>mPDF:</strong> <?php echo $mpdfInstalled ? 'Installed' : 'Not installed'; ?>
        </div>
        
        <div class="status-item <?php echo $autoloaderExists ? 'status-ok' : 'status-warning'; ?>">
            <strong>Autoloader:</strong> <?php echo $autoloaderExists ? 'Exists' : 'Does not exist'; ?>
        </div>
        
        <div class="status-item <?php echo $templatesDirExists ? 'status-ok' : 'status-warning'; ?>">
            <strong>Templates Directory:</strong> <?php echo $templatesDirExists ? 'Exists' : 'Created'; ?>
        </div>
        
        <div class="status-item <?php echo $imagesDirExists ? 'status-ok' : 'status-warning'; ?>">
            <strong>Images Directory:</strong> <?php echo $imagesDirExists ? 'Exists' : 'Created'; ?>
        </div>
        
        <div class="status-item <?php echo $configDirExists ? 'status-ok' : 'status-warning'; ?>">
            <strong>Config Directory:</strong> <?php echo $configDirExists ? 'Exists' : 'Created'; ?>
        </div>
        
        <div class="status-item <?php echo $tmpDirExists ? 'status-ok' : 'status-warning'; ?>">
            <strong>Temporary Directory:</strong> <?php echo $tmpDirExists ? 'Exists' : 'Created'; ?> (Permissions: <?php echo substr(sprintf('%o', fileperms(__DIR__ . '/tmp')), -4); ?>)
        </div>
        
        <div class="status-item <?php echo $srcDirExists && $modelsDirExists && $servicesDirExists && $utilsDirExists ? 'status-ok' : 'status-warning'; ?>">
            <strong>Source Directory Structure:</strong> <?php echo $srcDirExists && $modelsDirExists && $servicesDirExists && $utilsDirExists ? 'Complete' : 'Created'; ?>
        </div>
    </div>
    
    <div class="instructions">
        <h2>Installation Instructions</h2>
        
        <?php if (!$composerInstalled): ?>
            <p><strong>1. Install Composer</strong></p>
            <p>Follow the instructions at <a href="https://getcomposer.org/download/" target="_blank">https://getcomposer.org/download/</a> to install Composer.</p>
        <?php endif; ?>
        
        <?php if (!$mpdfInstalled): ?>
            <p><strong><?php echo $composerInstalled ? '1' : '2'; ?>. Install Dependencies</strong></p>
            <p>Run the following command from the module directory:</p>
            <code>cd <?php echo $GLOBALS['webroot']; ?>/interface/forms/general_dermatology</code><br>
            <code>composer install</code>
        <?php endif; ?>
        
        <p><strong>Next Steps</strong></p>
        <p>Once all dependencies are installed, you can test the module by accessing:</p>
        <p><a href="<?php echo $GLOBALS['webroot']; ?>/interface/forms/general_dermatology/test_pdf.php">Test PDF Generation</a></p>
        
        <p>To use the module with actual patient data, access it through:</p>
        <p><code><?php echo $GLOBALS['webroot']; ?>/interface/forms/general_dermatology/printable.php?formid=[FORM_ID]&visitid=[VISIT_ID]</code></p>
        
        <p>For more information, please refer to the <a href="<?php echo $GLOBALS['webroot']; ?>/interface/forms/general_dermatology/README.md">README</a>.</p>
    </div>
</body>
</html>
