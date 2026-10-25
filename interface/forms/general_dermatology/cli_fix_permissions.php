<?php
/**
 * Command-line script to fix permissions for the mPDF temporary directory
 * 
 * This script can be run from the command line without requiring an OpenEMR session
 */

// Define paths
$moduleDir = __DIR__;
$tempDir = $moduleDir . '/tmp';

echo "Fixing permissions for General Dermatology module temporary directories...\n";

// Create temp directory if it doesn't exist
if (!is_dir($tempDir)) {
    if (mkdir($tempDir, 0777, true)) {
        echo "Created temporary directory: $tempDir\n";
    } else {
        echo "Failed to create temporary directory: $tempDir\n";
    }
} else {
    echo "Temporary directory already exists: $tempDir\n";
}

// Set permissions on temp directory
if (chmod($tempDir, 0777)) {
    echo "Set permissions on temporary directory to 0777\n";
} else {
    echo "Failed to set permissions on temporary directory\n";
}

// Check current permissions
echo "Current permissions on $tempDir: " . substr(sprintf('%o', fileperms($tempDir)), -4) . "\n";

// Check if directory is writable
if (is_writable($tempDir)) {
    echo "Temporary directory is writable\n";
} else {
    echo "Temporary directory is NOT writable\n";
    
    // Get directory owner if possible
    if (function_exists('posix_getpwuid')) {
        $owner = posix_getpwuid(fileowner($tempDir));
        $dirGroup = posix_getgrgid(filegroup($tempDir));
        
        echo "Directory owned by: " . $owner['name'] . ":" . $dirGroup['name'] . "\n";
    }
}

// Test file creation
$testFile = $tempDir . '/test.txt';
$result = file_put_contents($testFile, 'Test file creation');
if ($result !== false) {
    echo "Successfully created test file in temporary directory\n";
    unlink($testFile); // Clean up
} else {
    echo "Failed to create test file in temporary directory\n";
}

echo "Permissions check complete.\n";
?>
