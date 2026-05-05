<?php
/**
 * Debug script for MedExAPI configuration
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

header('Content-Type: text/plain');

echo "=== MedEx API Debug ===\n\n";

// Check database directly
echo "1. Checking medex_prefs table:\n";
$prefs = sqlQuery("SELECT ME_api_key, MedEx_id, ME_username FROM medex_prefs WHERE ME_username IS NOT NULL AND ME_api_key IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1");
echo "   Query result: " . print_r($prefs, true) . "\n";
echo "   ME_api_key length: " . strlen($prefs['ME_api_key'] ?? '') . "\n";
echo "   MedEx_id: " . ($prefs['MedEx_id'] ?? 'NULL') . "\n";
echo "   ME_username: " . ($prefs['ME_username'] ?? 'NULL') . "\n\n";

// Check globals
echo "2. Checking \$GLOBALS array:\n";
echo "   medex_api_key: " . (isset($GLOBALS['medex_api_key']) && !empty($GLOBALS['medex_api_key']) ? 'SET (len: ' . strlen($GLOBALS['medex_api_key']) . ')' : 'NOT SET OR EMPTY') . "\n";
echo "   medex_practice_id: " . ($GLOBALS['medex_practice_id'] ?? 'NOT SET') . "\n";
echo "   medex_server_url: " . ($GLOBALS['medex_server_url'] ?? 'NOT SET') . "\n\n";

// Load the API class
echo "3. Loading MedExAPI class:\n";
require_once(__DIR__ . '/../src/MedExAPI.php');
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

echo "   isConfigured(): " . ($api->isConfigured() ? 'YES' : 'NO') . "\n\n";

// Try connection test
echo "4. Testing connection:\n";
$result = $api->testConnection();
echo "   Result: " . print_r($result, true) . "\n";

if ($api->getLastError()) {
    echo "   Last Error: " . $api->getLastError() . "\n";
}
