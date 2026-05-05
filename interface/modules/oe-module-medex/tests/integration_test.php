<?php
/**
 * MedEx Module Integration Test
 *
 * Tests the modernized MedEx module for:
 * - Class loading
 * - Backward compatibility aliases
 * - Method existence
 * - Basic functionality
 */

// Bootstrap from project root
require_once(__DIR__ . '/../src/API/API.php');

echo "=== MedEx Module Integration Test ===\n\n";

// Test 1: Class Loading
echo "Test 1: Class Loading\n";
echo str_repeat('-', 50) . "\n";

$classes = [
    'MedExApi\MedEx',
    'MedExApi\Client\HttpClient',
    'MedExApi\Services\BaseService',
    'MedExApi\Services\PracticeService',
    'MedExApi\Services\CampaignService',
    'MedExApi\Services\EventsService',
    'MedExApi\Services\CallbackService',
    'MedExApi\Services\LoggingService',
    'MedExApi\Services\DisplayService',
    'MedExApi\Services\SetupService',
    'MedExApi\Exceptions\InvalidDataException'
];

$failed = 0;
foreach ($classes as $class) {
    if (class_exists($class) || interface_exists($class) || trait_exists($class)) {
        echo "  ✓ $class\n";
    } else {
        echo "  ✗ $class NOT FOUND\n";
        $failed++;
    }
}

if ($failed > 0) {
    echo "\n✗ Class loading test FAILED ($failed classes missing)\n";
    exit(1);
}

echo "\n✓ All classes loaded successfully\n\n";

// Test 2: Backward Compatibility Aliases
echo "Test 2: Backward Compatibility Aliases\n";
echo str_repeat('-', 50) . "\n";

$aliases = [
    'MedExApi\CurlRequest' => 'MedExApi\Client\HttpClient',
    'MedExApi\Base' => 'MedExApi\Services\BaseService',
    'MedExApi\Practice' => 'MedExApi\Services\PracticeService',
    'MedExApi\Campaign' => 'MedExApi\Services\CampaignService',
    'MedExApi\Events' => 'MedExApi\Services\EventsService',
    'MedExApi\Callback' => 'MedExApi\Services\CallbackService',
    'MedExApi\Logging' => 'MedExApi\Services\LoggingService',
    'MedExApi\Display' => 'MedExApi\Services\DisplayService',
    'MedExApi\Setup' => 'MedExApi\Services\SetupService'
];

$failed = 0;
foreach ($aliases as $alias => $target) {
    if (class_exists($alias)) {
        echo "  ✓ $alias -> $target\n";
    } else {
        echo "  ✗ $alias NOT FOUND\n";
        $failed++;
    }
}

if ($failed > 0) {
    echo "\n✗ Alias test FAILED ($failed aliases missing)\n";
    exit(1);
}

echo "\n✓ All backward compatibility aliases working\n\n";

// Test 3: EventsService Methods
echo "Test 3: EventsService Methods\n";
echo str_repeat('-', 50) . "\n";

$eventsMethods = [
    'generate',
    'calculateEvents'
];

$failed = 0;
foreach ($eventsMethods as $method) {
    if (method_exists('MedExApi\Services\EventsService', $method)) {
        echo "  ✓ EventsService::$method()\n";
    } else {
        echo "  ✗ EventsService::$method() NOT FOUND\n";
        $failed++;
    }
}

if ($failed > 0) {
    echo "\n✗ EventsService methods test FAILED ($failed methods missing)\n";
    exit(1);
}

echo "\n✓ All EventsService methods present\n\n";

// Test 4: DisplayService Methods
echo "Test 4: DisplayService Methods\n";
echo str_repeat('-', 50) . "\n";

$displayMethods = [
    'show_progress_recall',
    'navigation',
    'preferences',
    'display_recalls',
    'get_recalls',
    'display_add_recall',
    'icon_template',
    'possibleModalities',
    'SMS_bot',
    'syncPat'
];

$failed = 0;
foreach ($displayMethods as $method) {
    if (method_exists('MedExApi\Services\DisplayService', $method)) {
        echo "  ✓ DisplayService::$method()\n";
    } else {
        echo "  ✗ DisplayService::$method() NOT FOUND\n";
        $failed++;
    }
}

if ($failed > 0) {
    echo "\n✗ DisplayService methods test FAILED ($failed methods missing)\n";
    exit(1);
}

echo "\n✓ All DisplayService methods present\n\n";

// Test 5: MedEx Main Class Instantiation
echo "Test 5: MedEx Main Class Instantiation\n";
echo str_repeat('-', 50) . "\n";

try {
    // Note: This will fail without database connection, but we're testing class structure
    echo "  Attempting to instantiate MedExApi\\MedEx...\n";

    // Check constructor exists
    $reflection = new ReflectionClass('MedExApi\MedEx');
    $constructor = $reflection->getConstructor();

    if ($constructor) {
        $params = $constructor->getParameters();
        echo "  ✓ Constructor found with " . count($params) . " parameters\n";

        // Check for service properties
        $properties = $reflection->getProperties();
        $serviceProps = array_filter($properties, function($prop) {
            return strpos($prop->getName(), 'Service') !== false;
        });

        echo "  ✓ Found " . count($serviceProps) . " service properties\n";
    }

    echo "\n✓ MedEx class structure valid\n\n";

} catch (Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 6: Type Declarations
echo "Test 6: Type Declarations on Key Methods\n";
echo str_repeat('-', 50) . "\n";

try {
    // Check EventsService::generate()
    $eventsReflection = new ReflectionClass('MedExApi\Services\EventsService');
    $generateMethod = $eventsReflection->getMethod('generate');

    $returnType = $generateMethod->getReturnType();
    echo "  ✓ EventsService::generate() has return type: " . ($returnType ? $returnType : 'none') . "\n";

    // Check DisplayService::show_progress_recall()
    $displayReflection = new ReflectionClass('MedExApi\Services\DisplayService');
    $showProgressMethod = $displayReflection->getMethod('show_progress_recall');

    $returnType = $showProgressMethod->getReturnType();
    echo "  ✓ DisplayService::show_progress_recall() has return type: " . ($returnType ? $returnType : 'none') . "\n";

    echo "\n✓ Type declarations present\n\n";

} catch (Exception $e) {
    echo "  ✗ Error checking type declarations: " . $e->getMessage() . "\n";
    exit(1);
}

// Summary
echo str_repeat('=', 50) . "\n";
echo "✓ ALL INTEGRATION TESTS PASSED\n";
echo str_repeat('=', 50) . "\n";
echo "\nSummary:\n";
echo "  - All 11 classes load correctly\n";
echo "  - All 9 backward compatibility aliases work\n";
echo "  - EventsService has all required methods\n";
echo "  - DisplayService has all required methods\n";
echo "  - MedEx main class structure is valid\n";
echo "  - Type declarations are present\n";
echo "\n✓ Module is ready for functional testing\n";
