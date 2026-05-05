<?php
// Simple test file to see if basic PHP works
echo "<!DOCTYPE html><html><head><title>Test</title></head><body>";
echo "<h1>Status Test</h1>";
echo "<p>If you see this, the file is accessible.</p>";

try {
    require_once(__DIR__ . "/../../../../globals.php");
    echo "<p>✓ Globals loaded</p>";
} catch (\Exception $e) {
    echo "<p>✗ Globals error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

try {
    require_once(__DIR__ . '/../src/MedExAPI.php');
    echo "<p>✓ MedExAPI loaded</p>";
} catch (\Exception $e) {
    echo "<p>✗ MedExAPI error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

try {
    require_once(__DIR__ . '/../src/UpdateManager.php');
    echo "<p>✓ UpdateManager loaded</p>";
} catch (\Exception $e) {
    echo "<p>✗ UpdateManager error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

try {
    $api = new \OpenEMR\Modules\MedEx\MedExAPI();
    echo "<p>✓ MedExAPI instantiated</p>";
} catch (\Exception $e) {
    echo "<p>✗ MedExAPI instantiation error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

try {
    $updateManager = new \OpenEMR\Modules\MedEx\UpdateManager();
    echo "<p>✓ UpdateManager instantiated</p>";
} catch (\Exception $e) {
    echo "<p>✗ UpdateManager instantiation error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

try {
    $isConfigured = $api->isConfigured();
    echo "<p>✓ isConfigured() called: " . ($isConfigured ? 'true' : 'false') . "</p>";
} catch (\Exception $e) {
    echo "<p>✗ isConfigured() error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

try {
    $connectionStatus = $api->testConnection();
    echo "<p>✓ testConnection() called</p>";
    echo "<pre>" . htmlspecialchars(print_r($connectionStatus, true)) . "</pre>";
} catch (\Exception $e) {
    echo "<p>✗ testConnection() error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
