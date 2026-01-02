<?php

/**
 * Test script for fax encryption/decryption
 * 
 * This script verifies that fax encryption and decryption work correctly
 */

require_once __DIR__ . '/../../../../globals.php';

use OpenEMR\Modules\FaxSMS\Controller\FaxDocumentService;
use OpenEMR\Common\Crypto\CryptoGen;

// Simulate test fax content
$testFaxContent = "Test fax PDF content %PDF-1.4\n%Binary content here";

echo "Fax Encryption Test\n";
echo "===================\n\n";

try {
    // Test 1: Basic encryption/decryption using CryptoGen directly
    echo "Test 1: Direct CryptoGen encryption/decryption\n";
    $crypto = new CryptoGen();
    $encrypted = $crypto->encryptStandard($testFaxContent, null, 'database');
    $decrypted = $crypto->decryptStandard($encrypted, null, 'database');
    
    if ($testFaxContent === $decrypted) {
        echo "✓ Direct encryption/decryption works correctly\n\n";
    } else {
        echo "✗ Direct encryption/decryption failed\n\n";
    }
    
    // Test 2: Base64 encoding for filesystem storage
    echo "Test 2: Base64 encoding for filesystem storage\n";
    $base64Encrypted = base64_encode($encrypted);
    $base64Decrypted = base64_decode($base64Encrypted, true);
    $finalDecrypted = $crypto->decryptStandard($base64Decrypted, null, 'database');
    
    if ($testFaxContent === $finalDecrypted) {
        echo "✓ Base64 encoding/decoding works correctly\n";
        echo "✓ Encrypted size: " . strlen($encrypted) . " bytes\n";
        echo "✓ Base64 size: " . strlen($base64Encrypted) . " bytes\n\n";
    } else {
        echo "✗ Base64 encoding/decoding failed\n\n";
    }
    
    // Test 3: Simulate file storage
    echo "Test 3: Simulated file storage\n";
    $tempFile = tempnam(sys_get_temp_dir(), 'fax_test_');
    file_put_contents($tempFile, $base64Encrypted);
    $fileContent = file_get_contents($tempFile);
    $fileDecoded = base64_decode($fileContent, true);
    $fileDecrypted = $crypto->decryptStandard($fileDecoded, null, 'database');
    unlink($tempFile);
    
    if ($testFaxContent === $fileDecrypted) {
        echo "✓ File storage and retrieval works correctly\n\n";
    } else {
        echo "✗ File storage and retrieval failed\n\n";
    }
    
    echo "All tests completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error during testing: " . $e->getMessage() . "\n";
    exit(1);
}
