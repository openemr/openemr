<?php

// Simple test file to verify auto_prepend and auto_append are working
echo "Test script executed\n";

// Check and report if marker files exist at the end of execution
$prepend_marker = dirname(__DIR__) . '/coverage/PREPEND_EXECUTED';
$append_marker = dirname(__DIR__) . '/coverage/APPEND_EXECUTED';

// This runs before auto_append, so append marker shouldn't exist yet
if (file_exists($prepend_marker)) {
    echo "PREPEND marker exists: YES\n";
} else {
    echo "PREPEND marker exists: NO\n";
}

if (file_exists($append_marker)) {
    echo "APPEND marker exists: YES (unexpected at this point)\n";
} else {
    echo "APPEND marker exists: NO (expected - should be created after this script)\n";
}

// Log the auto_prepend/auto_append settings
echo "auto_prepend_file: " . ini_get('auto_prepend_file') . "\n";
echo "auto_append_file: " . ini_get('auto_append_file') . "\n";
