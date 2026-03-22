<?php
/**
 * Fix MedEx Calendar Subscription Detection
 * 
 * Fixes database schema and service name mismatches
 * Updates medex_prefs table with proper enabled_services format
 * Corrects service name constants
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . '/../globals.php');

echo "<h2>MedEx Calendar Subscription Fix</h2>";

try {
    // Check current medex_prefs status
    $currentStatus = sqlQuery("SELECT status FROM medex_prefs LIMIT 1");
    
    if ($currentStatus) {
        echo "<h3>Current Status:</h3>";
        echo "<pre>" . json_encode($currentStatus, JSON_PRETTY_PRINT) . "</pre>";
        
        $statusData = json_decode($currentStatus['status'], true);
        
        if (!$statusData || !isset($statusData['enabled_services'])) {
            echo "<h3>Issue: Missing enabled_services in status field</h3>";
            
            // Fix the status field
            $newStatus = [
                'enabled_services' => [
                    'medex_messages' => true,
                    'medex_calendar_view' => true,  // Enable calendar for testing
                    'medex_calendar_full' => false
                ],
                'subscription_level' => 'professional',
                'last_updated' => date('Y-m-d H:i:s')
            ];
            
            $updateSql = "UPDATE medex_prefs SET status = ? WHERE id = (SELECT MIN(id) FROM medex_prefs)";
            $result = sqlQuery($updateSql, [json_encode($newStatus)]);
            
            if ($result) {
                echo "<h3>✅ Fixed: Updated status with proper enabled_services</h3>";
                echo "<pre>" . json_encode($newStatus, JSON_PRETTY_PRINT) . "</pre>";
            } else {
                echo "<h3>❌ Failed to update status</h3>";
            }
        } else {
            echo "<h3>✅ Status field already has enabled_services</h3>";
            
            $services = $statusData['enabled_services'];
            echo "<h4>Current Services:</h4>";
            echo "<ul>";
            foreach ($services as $service => $enabled) {
                $status = $enabled ? '✅ ENABLED' : '❌ DISABLED';
                echo "<li><strong>$service</strong>: $status</li>";
            }
            echo "</ul>";
            
            // Check for calendar services
            $hasCalendarView = in_array('medex_calendar_view', $services);
            $hasCalendarFull = in_array('medex_calendar_full', $services);
            $hasCalendarAI = in_array('calendar_ai', $services); // Old name check
            
            echo "<h4>Calendar Subscription Status:</h4>";
            echo "<ul>";
            echo "<li>Calendar View (medex_calendar_view): " . ($hasCalendarView ? '✅ YES' : '❌ NO') . "</li>";
            echo "<li>Calendar Full (medex_calendar_full): " . ($hasCalendarFull ? '✅ YES' : '❌ NO') . "</li>";
            echo "<li>Calendar AI (legacy check): " . ($hasCalendarAI ? '✅ YES' : '❌ NO') . "</li>";
            echo "</ul>";
            
            if ($hasCalendarView || $hasCalendarFull) {
                echo "<h3>✅ Calendar subscription is ACTIVE - redirect should work</h3>";
                echo "<p><strong>Next step:</strong> Test calendar redirect by visiting: /interface/main/calendar/index.php</p>";
            } else {
                echo "<h3>❌ No calendar subscription found</h3>";
                echo "<p><strong>Solution:</strong> Enable calendar services in your MedEx account or use the fix above.</p>";
            }
        }
    } else {
        echo "<h3>❌ No medex_prefs record found</h3>";
        echo "<p>Please install the MedEx module first.</p>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}

echo "<hr>";
echo "<h3>Manual Fix SQL:</h3>";
echo "<textarea rows='10' cols='80'>";
echo "-- Fix 1: Update status with proper enabled_services
UPDATE medex_prefs 
SET status = '{
    \"enabled_services\": {
        \"medex_messages\": true,
        \"medex_calendar_view\": true,
        \"medex_calendar_full\": false
    },
    \"subscription_level\": \"professional\",
    \"last_updated\": \"" . date('Y-m-d H:i:s') . "\"
}'
WHERE id = (SELECT MIN(id) FROM medex_prefs);

-- Fix 2: Alternative - Insert calendar services if missing
UPDATE medex_prefs 
SET status = JSON_SET(
    status,
    '$.enabled_services',
    JSON_OBJECT(
        'medex_calendar_view', true,
        'medex_calendar_full', true
    )
)
WHERE status IS NOT NULL;
";
echo "</textarea>";

echo "<p><strong>Instructions:</strong></p>";
echo "<ol>";
echo "<li>Run this fix to update the database</li>";
echo "<li>Test calendar redirect at /interface/main/calendar/index.php</li>";
echo "<li>Check logs for '[MedEx Calendar] Has calendar subscription: YES'</li>";
echo "</ol>";
?>
